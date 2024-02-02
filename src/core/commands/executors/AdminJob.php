<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\managers\jobs\JobsManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class AdminJob extends Executor
{

    public function __construct(string $name = "adminjob", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("adminjob.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (empty($args[0])) {
            $sender->sendMessage(Messages::message("§cUtilisation: /adminjob <player>"));
            return;
        }

        $target = $sender->getServer()->getPlayerByPrefix($args[0]);
        if ($target == null) {
            $sender->sendMessage(Messages::message("§cJoueur introuvable"));
            return;
        }

        $this->menu($sender, $target);
    }


    private function menu(Player $player, Player $target): void
    {
        $jobManager = Main::getInstance()->getJobsManager();
        $jobs = $jobManager->getAllJobs($target);

        $buttons = [];
        foreach (array_keys($jobs) as $job) {
            $buttons[] = new Button("Métier {$job}");
        }

        $player->sendForm(new MenuForm("§l§eAdminJob",
            "",
            $buttons,
            function (Player $player, Button $button) use ($jobs, $target, $jobManager): void {
                $this->job($player, $target, str_replace("Métier ", "", $button->getText()), $jobs, $jobManager);
            }));
    }


    private function job(Player $player, Player $target, string $job, array $jobs, JobsManager $jobsManager): void
    {
        $player->sendForm(new MenuForm("§l§eAdminJob - {$target->getName()} - {$job}",
            "",
            [
                new Button("§l§eVoir le métier"),
                new Button("§l§eReset le métier"),
                new Button("§l§eAjouter de l'expérience"),
                new Button("§l§eAjouter des niveaux"),
                new Button("§l§eRetirer des niveaux"),
                new Button("§l§eRetour")
            ],
            function (Player $player, Button $button) use ($target, $jobs, $job, $jobsManager): void {
                if ($button->getValue() == 0) {
                    $this->jobInfo($player, $target, $job, $jobs);
                }
                if ($button->getValue() == 1) {
                    $this->confirmReset($player, $target, $job, $jobsManager);
                }
                if ($button->getValue() == 2) {
                    $this->experience($player, $target, $jobsManager, $job, $jobs, "ajouter");
                }
                if ($button->getValue() == 3) {
                    $this->level($player, $target, $jobsManager, $job, $jobs, "ajouter");
                }
                if ($button->getValue() == 4) {
                    $this->level($player, $target, $jobsManager, $job, $jobs, "retirer");
                }
                if ($button->getValue() == 5) {
                    $this->menu($player, $target);
                }
            }));
    }


    private function confirmReset(Player $player, Player $target, string $job, JobsManager $jobsManager): void
    {
        $player->sendForm(new MenuForm("§l§eAdminJob - {$target->getName()}",
            "",
            [
                new Button("§l§eConfirmer"),
                new Button("§l§eAnnuler")
            ],
            function (Player $player, Button $button) use ($target, $job, $jobsManager): void {
                if ($button->getValue() == 0) {
                    $jobsManager->resetJob($target, $job);
                    $player->sendMessage(Messages::message("§aLe métier {$job} a été reset pour {$target->getName()}"));
                }
                if ($button->getValue() == 1) {
                    $this->menu($player, $target);
                }
            }));
    }


    private function experience(Player $player, Player $target, JobsManager $jobsManager, string $job, array $jobs, string $option): void
    {
        $player->sendForm(new CustomForm("§l§eAdminJob - {$target->getName()}",
            [
                new Label(ucfirst($option) . " de l'expérience pour le métier {$job}"),
                new Input("§l§eExpérience", "montant de l'expérience à " . $option)
            ],
            function (Player $player, CustomFormResponse $response) use ($jobsManager, $target, $jobs, $job, $option): void {
                list($value) = $response->getValues();
                if (!is_numeric($value)) {
                    $this->experience($player, $target, $jobsManager, $job, $jobs, $option);
                } else {
                    $this->confirm($player, $target, $jobsManager, $job, $option, "xp", $value);
                }
            }
        ));
    }


    private function level(Player $player, Player $target, JobsManager $jobsManager, string $job, array $jobs, string $option): void
    {
        $player->sendForm(new CustomForm("§l§eAdminJob - {$target->getName()}",
            [
                new Label(ucfirst($option) . " des niveaux pour le métier {$job}"),
                new Input("§l§eNiveaux", "montant de niveaux à " . $option)
            ],
            function (Player $player, CustomFormResponse $response) use ($jobsManager, $target, $jobs, $job, $option): void {
                list($value) = $response->getValues();
                if (!is_numeric($value)) {
                    $this->level($player, $target, $jobsManager, $job, $jobs, $option);
                } else {
                    $this->confirm($player, $target, $jobsManager, $job, $option, "lvl", $value);
                }
            }
        ));
    }

    private function jobInfo(Player $player, Player $target, string $job, array $jobs): void
    {
        $lvl = $jobs[$job]["lvl"];
        if ($lvl == 0) {
            $xpRestate = JobsManager::XP_LVL[2];
            $xpRequired = JobsManager::XP_LVL[2];
        } else {
            $xpRequired = JobsManager::XP_LVL[$jobs[$job]["lvl"] + 1];
            $xpRestate = JobsManager::XP_LVL[$jobs[$job]["lvl"] + 1] - $jobs[$job]["xp"];
        }


        $player->sendForm(new CustomForm("§l§eAdminJob - {$target->getName()}",
            [
                new Label("§l§eNom: §f{$job}"),
                new Label("§l§eNiveau: §f{$jobs[$job]["lvl"]}"),
                new Label("§l§eExpérience: §f{$jobs[$job]["xp"]}"),
                new Label("§l§eExpérience requise: §f" . $xpRequired),
                new Label("§l§eExpérience restante: §f" . $xpRestate),
            ],
            function (Player $player, CustomFormResponse $response) use ($target, $jobs, $job): void {

            }
        ));
    }


    private function confirm(Player $player, Player $target, JobsManager $jobsManager, string $job, string $option, string $type, int $value): void
    {
        $player->sendForm(new MenuForm("§l§eAdminJob - {$target->getName()}",
            "",
            [
                new Button("§l§eConfirmer"),
                new Button("§l§eAnnuler")
            ],
            function (Player $player, Button $button) use ($jobsManager, $target, $job, $option, $type, $value): void {
                if ($button->getValue() == 0) {
                    if ($type == "xp") {
                        if ($option == "ajouter") {
                            $jobsManager->addXp($target, $job, $value, true);
                        }
                    } else if ($type == "lvl") {
                        if ($option == "ajouter") {
                            $jobsManager->addLevel($target, $job, $value);
                        } else if ($option == "retier") {
                            $jobsManager->removeLevel($target, $job, $value);
                        }
                    }
                    if ($target->isConnected()) {
                        $target->sendMessage(Messages::message("§aVotre métier {$job} a été modifié par un administrateur"));
                    }
                }
                if ($button->getValue() == 1) {
                    $this->menu($player, $target);
                }
            }));
    }



    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}