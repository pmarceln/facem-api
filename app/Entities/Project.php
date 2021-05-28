<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {

    protected $table = "projects";

    public $timestamps = false;

    public function photos()
    {
        return $this->hasMany('App\Entities\Photo', 'idproject', 'id')->orderBy('order');
    }
}
