<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllLogs extends Model
{
    use HasFactory;
    protected $fillable = ['log_name', 'log_description'];

}
