<?php
namespace ContentFix;

class FieldDataBody extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'field_data_body';
    protected $primaryKey = 'revision_id';
    public $timestamps = false;
}