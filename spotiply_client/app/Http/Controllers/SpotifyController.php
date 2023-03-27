<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class SpotifyController extends Controller
{
    public function get_access_token(Request $request)
    {
        $conf = new \RdKafka\Conf();
        // get the access token from the session
        $access_token = session('spotify_access_token');

        // access 5 of the user's latest tracks
        $client = new Client([
            'base_uri' => 'https://api.spotify.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json'
            ]
        ]);

        $response = $client->request('GET', 'me/player/recently-played');
        $tracks = json_decode($response->getBody(), true);
        // sent to kafka topic 'spotify tracks'
        $conf->set('metadata.broker.list', 'localhost:9092');
        $rk = new \RdKafka\Producer($conf);
        $rk->addBrokers("localhost:9092");
        $topic = $rk->newTopic("latest_tracks");

        foreach($tracks['items'] as $i => $track) {
            $track = $tracks['items'][$i]['track'];

            $audio_feature = $client->request('GET', 'audio-features/' . $track['id']);
            $track['audio_features'] = json_decode($audio_feature->getBody(), true);

            //only send the latest track
            $msg = json_encode([
                'track_id' => $track['id'],
                'track_name' => $track['name'],
                'artist' => $track['artists'][0]['name'],
                'album_image' => $track['album']['images'][0]['url'],
                'danceability' => $track['audio_features']['danceability'],
                'energy' => $track['audio_features']['energy'],
                'loudness' => $track['audio_features']['loudness'],
                'mode' => $track['audio_features']['mode'],
                'speechiness' => $track['audio_features']['speechiness'],
                'acousticness' => $track['audio_features']['acousticness'],
                'instrumentalness' => $track['audio_features']['instrumentalness'],
                'liveness' => $track['audio_features']['liveness'],
                'valence' => $track['audio_features']['valence'],
                'tempo' => $track['audio_features']['tempo'],
            ]);

            //$msg = $track['id'].','.$track['name'].','.$track['album']['images'][0]['url'].','.$track['audio_features']['danceability'].','.$track['audio_features']['energy'].','.$track['audio_features']['loudness'].','.$track['audio_features']['mode'].','.$track['audio_features']['speechiness'].','.$track['audio_features']['acousticness'].','.$track['audio_features']['instrumentalness'].','.$track['audio_features']['liveness'].','.$track['audio_features']['valence'].','.$track['audio_features']['tempo'];

            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $msg);
            $rk->poll(0);
        }
        $rk->flush(1000);

        $data['tracks'] = $tracks['items'];

        // Apply the web middleware to persist session data across requests
        $this->middleware('web');

        // Access the session variable
        $chara = Cache::get('chara');
        $rec = Cache::get('rec');
        $data['rec'] = $rec;
        //reverse the array so that the latest track is on top
        if ($data['rec'] != null)
            $data['rec'] = array_reverse($data['rec']);

        //only show the top 5 recommendations
        if ($data['rec'] != null)
            $data['rec'] = array_slice($data['rec'], 0, 5);

        if ($data['rec'] == null)
            $data['rec'] = [];

        if ($chara != null){
            foreach($chara as $key => $value)
                $data['chara'][$key] = number_format($value, 2);
            $data['chara'] = [$chara['danceability'], $chara['energy'], $chara['speechiness'], $chara['acousticness'], $chara['instrumentalness'], $chara['liveness']];
        }
        else
            $data['chara'] = [0,0,0,0,0,0];

        return view('latest', $data);

    }

}
