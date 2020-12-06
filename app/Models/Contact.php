<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacts';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
      'created_at', 'updated_at'
    ];

    protected $with = ['custom_attributes'];

    public function custom_attributes() 
    {
      return $this->hasMany('App\Models\CustomAttribute', 'contact_id');
    }
}