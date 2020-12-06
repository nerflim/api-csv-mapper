<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomAttribute extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'custom_attributes';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
      'created_at', 'updated_at'
    ];
}