<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class SpotifyController extends Controller
{
    public function get_access_token()
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


            //sent to kafka topic 'spotify tracks'
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
        // dd($data);
        $conf = new \RdKafka\Conf();
        $conf->set('metadata.broker.list', 'localhost:9092');
        $rk = new \RdKafka\Consumer($conf);
        $rk->addBrokers("localhost:9092");
        $topic = $rk->newTopic("recommendations");
        $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);
        $msg = $topic->consume(0, 1000);
        $msg = json_decode($msg->payload, true);
        $rec = [];
        //get session

        $session = session('rec');
        foreach($session as $i => $track) {
            $rec[] = $session[$i];
        }
        foreach($msg as $i => $track) {
            $rec[] = $msg[$i];
        }
        // add to session
        session(['rec' => $rec]);
        $data['rec'] = $rec;

        return view('latest', $data);

    }
}
