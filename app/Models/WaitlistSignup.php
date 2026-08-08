<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'name', 'shop_name', 'source'])]
class WaitlistSignup extends Model
{
    //
}
