<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateStaticFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatestaticfiles {--update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =
        'Update the static files that the app uses to get data and information about static objects in the game.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Getting current League of Legends Version...');
        // get the current LoL version
        $version_file_content = file_get_contents('https://ddragon.leagueoflegends.com/api/versions.json');
        $version_file_content = json_decode($version_file_content);

        $current_lol_version = $version_file_content[0];
        $this->info('Current League of Legends Version: ' . $current_lol_version);

        // get the champion data and set it to the champion.json file
        $this->info('Getting up to date champion data...');
        $champion_data = file_get_contents('http://ddragon.leagueoflegends.com/cdn/'.$current_lol_version.'/data/en_US/champion.json');
        file_put_contents('./public/jsonfiles/champion.json', $champion_data);
        $this->info('Success!');

        // get the item data and set it to the item.json file
        $this->info('Getting up to date item data...');
        $item_data = file_get_contents('http://ddragon.leagueoflegends.com/cdn/'.$current_lol_version.'/data/en_US/item.json');
        file_put_contents('./public/jsonfiles/item.json', $item_data);
        $this->info('Success!');

        // get the item data and set it to the item.json file
        $this->info('Getting up to date summoner spell data...');
        $summoner_spell_data = file_get_contents('http://ddragon.leagueoflegends.com/cdn/'.$current_lol_version.'/data/en_US/summoner.json');
        file_put_contents('./public/jsonfiles/summonerspells.json', $summoner_spell_data);
        $this->info('Success!');

        if ($this->option('update')) {
            $this->info('Updating the javascript version number constant...');
            $javascript_file_contents = file_get_contents('./resources/assets/js/mixin.js');
            $regex_expression = '/API_VERSION: \'.{1,2}.{1,2}.{1,2}\'/';
            $new_file_contents = preg_replace($regex_expression, 'API_VERSION: \''.$current_lol_version.'\'', $javascript_file_contents);
            file_put_contents('./resources/assets/js/mixin.js', $new_file_contents);
        }
    }
}
