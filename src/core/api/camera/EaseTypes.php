<?php

namespace core\api\camera;

interface EaseTypes
{
    public const LINEAR = 0;
    public const SPRING = 1;
    public const IN_QUAD = 2;
    public const OUT_QUAD = 3;
    public const IN_OUT_QUAD = 4;
    public const IN_CUBIC = 5;
    public const OUT_CUBIC = 6;
    public const IN_OUT_CUBIC = 7;
    public const IN_QUART = 8;
    public const OUT_QUART = 9;
    public const IN_OUT_QUART = 10;
    public const IN_QUINT = 11;
    public const OUT_QUINT = 12;
    public const IN_OUT_QUINT = 13;
    public const IN_SINE = 14;
    public const OUT_SINE = 15;
    public const IN_OUT_SINE = 16;
    public const IN_EXPO = 17;
    public const OUT_EXPO = 18;
    public const IN_OUT_EXPO = 19;
    public const IN_CIRC = 20;
    public const OUT_CIRC = 21;
    public const IN_OUT_CIRC = 22;
    public const IN_BOUNCE = 23;
    public const OUT_BOUNCE = 24;
    public const IN_OUT_BOUNCE = 25;
    public const IN_BACK = 26;
    public const OUT_BACK = 27;
    public const IN_OUT_BACK = 28;
    public const IN_ELASTIC = 29;
    public const OUT_ELASTIC = 30;
    public const IN_OUT_ELASTIC = 31;
}