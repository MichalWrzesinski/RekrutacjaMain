<?php

namespace App\Query\Auth;

use App\Dto\Input\LoginByTokenInput;
use App\Dto\Output\AuthUserOutput;

interface FindAuthUserByTokenQueryInterface
{
    public function execute(LoginByTokenInput $input): ?AuthUserOutput;
}