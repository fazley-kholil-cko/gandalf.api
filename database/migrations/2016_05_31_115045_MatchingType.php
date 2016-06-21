<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MatchingType extends Migration
{
    public function up()
    {
        $tableColl = \DB::collection('tables');
        $tableColl->where('matching_type', 'first')->update(['$set' => ['matching_type' => "decision",]]);
        $tableColl->where('matching_type', 'all')->update(['$set' => ['matching_type' => "scoring",]]);
        $tableColl->where('matching_type', ['$exists' => false])->update(['$set' => ['matching_type' => "decision",]]);

    }

    public function down()
    {
        $tableColl = \DB::collection('tables');
        $tableColl->where('matching_type', 'decision')->update(['$set' => ['matching_type' => "first",]]);
        $tableColl->where('matching_type', 'scoring')->update(['$set' => ['matching_type' => "all",]]);
    }
}