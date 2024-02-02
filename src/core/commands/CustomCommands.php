<?php

namespace core\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

class CustomCommands extends Command
{
    public const ARG_FLAG_VALID = 0x100000;
    public const ARG_TYPE_INT = 0x01;
    public const ARG_TYPE_FLOAT = 0x03;
    public const ARG_TYPE_VALUE = 0x04;
    public const ARG_TYPE_WILDCARD_INT = 0x05;
    public const ARG_TYPE_OPERATOR = 0x06;
    public const ARG_TYPE_COMPARE_OPERATOR = 0x07;
    public const ARG_TYPE_TARGET = 0x08;
    public const ARG_TYPE_WILDCARD_TARGET = 0x0a;
    public const ARG_TYPE_FILEPATH = 0x11;
    public const ARG_TYPE_FULL_INTEGER_RANGE = 0x17;
    public const ARG_TYPE_EQUIPMENT_SLOT = 0x26;
    public const ARG_TYPE_STRING = 0x27;
    public const ARG_TYPE_INT_POSITION = 0x2f;
    public const ARG_TYPE_POSITION = 0x30;
    public const ARG_TYPE_MESSAGE = 0x33;
    public const ARG_TYPE_RAWTEXT = 0x35;
    public const ARG_TYPE_JSON = 0x39;
    public const ARG_TYPE_BLOCK_STATES = 0x43;
    public const ARG_TYPE_COMMAND = 0x46;
    public const ARG_FLAG_ENUM = 0x200000;
    public const ARG_FLAG_POSTFIX = 0x1000000;
    /**
     * @var CommandParameter[][]
     */
    protected array $overload = [];
    protected array $subCommandList = [];

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {

    }

    public function addOptionEnum(int $position, string $nameBeforePoint, bool $isOptional, string $nameAfterPoint, array $values): bool
    {

        $this->overload[0][$position] = CommandParameter::enum($nameBeforePoint, new CommandEnum($nameAfterPoint, $values), 0, $isOptional);
        $this->subCommandList[0] = $this->overload[0][0];
        $this->subCommandList[0] = $this->overload[0][0];
        return true;
    }

    public function addOption(int $position, string $nameBeforePoint, bool $isOptional, int $ARG_TYPE): bool
    {
        $this->overload[0][$position] = CommandParameter::standard($nameBeforePoint, $ARG_TYPE, 0, $isOptional);
        $this->subCommandList[0] = $this->overload[0][0];
        return true;
    }


    public function addSubCommand(int $position, string $subName): bool
    {
        $this->overload[$position][0] = CommandParameter::enum($subName, new CommandEnum($subName, [strtolower($subName) => strtolower($subName)]), 0, false);
        $this->subCommandList[$position] = $this->overload[$position][0];
        return true;
    }

    public function addSubCommandSubCommand(int $position, int $subCommand, string $subName): bool
    {
        $this->overload[$position][$subCommand] = CommandParameter::enum($subName, new CommandEnum($subName, [strtolower($subName) => strtolower($subName)]), 0, false);
        $this->subCommandList[$position] = $this->overload[$position][0];
        return true;
    }

    public function addSubCommandOption(int $subCommandPosition, int $optionPositon, string $nameBeforePoint, bool $isOptional, int $ARG_TYPE): bool
    {

        $this->overload[$subCommandPosition][$optionPositon] = CommandParameter::standard($nameBeforePoint, $ARG_TYPE, 0, $isOptional);
        $this->subCommandList[$subCommandPosition] = $this->overload[$subCommandPosition][0];
        return true;
    }

    public function addComment(int $subCommandPosition, int $optionPosition, string $comment)
    {
        $this->addSubCommandOptionEnum($subCommandPosition, $optionPosition, $comment, true, "GoldRush", []);
        return true;
    }

    public function addSubCommandOptionEnum(int $subCommandPosition, int $optionPositon, string $nameBeforePoint, bool $isOptional, string $nameAfterPoint, array $values): bool
    {
        foreach ($values as $index => $value) {
            $set[strtolower($index)] = strtolower($value);
        }

        $this->overload[$subCommandPosition][$optionPositon] = CommandParameter::enum($nameBeforePoint, new CommandEnum($nameAfterPoint, $values), 0, $isOptional);
        $this->subCommandList[$subCommandPosition] = $this->overload[$subCommandPosition][0];
        return true;
    }


    public function deleteCommandData(): void
    {
        $this->overload = [];
    }
}