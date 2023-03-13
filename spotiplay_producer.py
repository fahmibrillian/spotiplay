import spotipy
import json
from spotipy.oauth2 import SpotifyClientCredentials
from kafka import KafkaProducer
from pymongo import MongoClient
from time import sleep

client_id = 'b085145c95fb4dd48a5aafcbae7b92d9'
client_secret = '94a2ea5986b44c6189a604dee17aff7d'
client_credentials_manager = SpotifyClientCredentials(client_id, client_secret)
sp = spotipy.Spotify(client_credentials_manager = client_credentials_manager)


# set up Kafka producer
producer = KafkaProducer(bootstrap_servers=['localhost:9092'])

genres = [
    'indonesian indie',
    'indonesian pop',
    'indonesian r&b',
    'indonesian rock',
    'indonesian hip hop',
    'indonesian electronic',
    'indonesian jazz',
    'indonesian folk',
    'indonesian reggae',
    'indonesian metal',
    'indonesian punk',
    'indonesian alternative',
    'indonesian acoustic',
]

# get tracks from Spotify API and publish to Kafka topic
while True:
    for genre in genres:
        #incremental offset
        offset = 0
        while offset < 500:
            results = sp.search(q='genre:' + genre, type='track', limit=50, offset=offset)
            if(len(results['tracks']['items']) == 0):
                break
            print('genre: ' + genre + ', offset: ' + str(offset))
            for track in results['tracks']['items']:
                audio_features = sp.audio_features(track['id'])
                track['audio_features'] = audio_features[0]
                producer.send('spotify_tracks', json.dumps(track).encode('utf-8'))
                # sleep(0.5)
                offset += 1