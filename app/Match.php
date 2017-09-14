<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    public function participantIdentities() {
        return $this->hasMany('App\MatchParticipantIdentities', 'matchId', 'gameId');
    }

    public function participants() {
        return $this->hasMany('App\MatchParticipant','matchId', 'gameId');
    }

    public function teams() {
        return $this->hasMany('App\MatchTeam','matchId', 'gameId');
    }
}
