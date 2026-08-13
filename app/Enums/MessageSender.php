<?php

namespace App\Enums;

enum MessageSender: string
{
    case Manager = 'manager';
    case Client = 'client';
}
