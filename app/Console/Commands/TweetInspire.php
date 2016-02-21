<?php

namespace App\Console\Commands;

use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class TweetInspire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:tweet-inspire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tweet a quote';

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
        $quote = Collection::make([

            'Ils ne savaient pas que c’était impossible alors ils l’ont fait. - Mark Twain',
            'Tous les hommes pensent que le bonheur se trouve au sommet de la montagne alors qu’il réside dans la façon de la gravir. - Confucius',
            'Un voyage de mille lieues commence toujours par un premier pas. - Lao Tseu',
            'Ce qui est plus triste qu’une œuvre inachevée, c’est une œuvre jamais commencée. - Christinna Rosseti',
            'Il y a des gens qui disent qu’ils peuvent, et d’autres qu’ils ne peuvent pas. En général ils ont tous raison. - Henry Ford',
            'Tous les jours et à tout point de vue, je vais de mieux en mieux. - Emile Coué',
            'Quand on ne peut revenir en arrière, on ne doit que se préoccuper de la meilleure manière d’aller de l’avant. - Paulo Coelho',
            'L’échec est seulement l’opportunité de recommencer d’une façon plus intelligente. - Henry Ford',
            'L’action n’apporte pas toujours le bonheur, sans doute, mais il n’y a pas de bonheur sans action. - Benjamin Disraeli',
            'Seuls ceux qui se risqueront à peut-être aller trop loin sauront jusqu’où il est possible d’aller. - Thomas Stearns Eliot',
            'Il faut viser la lune, parce qu’au moins si vous échouez, vous finissez dans les étoiles. - Oscar Wilde',
            'Les gagnants trouvent des moyens, les perdants des excuses. - Franklin Roosevelt',
            'Croyez en vous-même, en l’humanité, au succès de vos entreprises. Ne craignez rien ni personne. - Baronne Staffe',
            'Ils le peuvent, parce qu’ils pensent qu’ils le peuvent. - Virgile',
            'Si vous pouvez le rêver, vous pouvez le faire. - Walt Disney',
            'Les grandes réalisations sont toujours précédées par de grandes pensées. - Steve Jobs',
            'C’est à l’âge de dix ans que j’ai gagné Wimbledon pour la première fois… dans ma tête. - André Agassi',
            'Il n’est pas de vent favorable pour celui qui ne sait pas où il va. - Sénèque',
            'Il faut se concentrer sur ce qu’il nous reste et non sur ce qu’on a perdu. - Yann Arthus Bertrand',
            'Il est toujours trop tôt pour abandonner. - Norman Vincent Peale',
            'Il n’y a qu’une façon d’échouer, c’est d’abandonner avant d’avoir réussi. - Georges Clemenceau',
            'Le but n’est pas tout. Chaque pas vers le but est un but. Ce sont tous les petits buts qui font le but. - Confucius',
            'En suivant le chemin qui s’appelle plus tard, nous arrivons sur la place qui s’appelle jamais. - Sénèque',
            'Exposez-vous à vos peurs les plus profondes et après celà, la peur ne pourra plus vous atteindre. - Jim Morrisson',
            'Appréciez d’échouer, et apprenez de l’échec, car on n’apprend rien de ses succès. - James Dyson',
            'Le succès, c’est d’aller d’échec en échec sans perdre son enthousiasme. - Winston Churchill',
            'Si tu dors et que tu rêves que tu dors, il faut que tu te réveilles deux fois pour te lever. - Jean Claude Vandamme'

        ])->random();

        Log::info('Tweeting quote : '.$quote);

        \Twitter::postTweet(['status' => $quote, 'format' => 'array']);
    }
}
