import spotipy
from spotipy.oauth2 import SpotifyOAuth

client_id = 'b085145c95fb4dd48a5aafcbae7b92d9'
client_secret = '94a2ea5986b44c6189a604dee17aff7d'

# Set up Spotipy with OAuth credentials
sp = spotipy.Spotify(auth_manager=SpotifyOAuth(client_id=client_id,
                                               client_secret=client_secret,
                                               cache_path = '.spotipyoauthcache',
                                               redirect_uri='http://127.0.0.1:8000/spotify-callback'))

# Get the user's most recently played track
recently_played = sp.current_user_recently_played(limit=10)

# Extract the relevant information from the response
track_name = recently_played['items'][0]['track']['name']
artist_name = recently_played['items'][0]['track']['artists'][0]['name']

# Print the results
print(f"Your latest played track is '{track_name}' by {artist_name}")