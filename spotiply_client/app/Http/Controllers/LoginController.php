<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /**
     * Redirect the user to the Spotify authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToSpotifyProvider()
    {
        return Socialite::driver('spotify')->scopes(['user-read-email','user-read-playback-position','user-top-read','user-read-recently-played'])->redirect();
    }

    /**
     * Obtain the user information from Spotify.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleSpotifyProviderCallback(Request $request)
    {
        $spotifyUser = Socialite::driver('spotify')->user();

        // You can now use the $spotifyUser object to retrieve the user's information from Spotify
        // cache the user's access token
        $request->session()->put('spotify_access_token', $spotifyUser->token);

        return redirect('/spotify');
    }
}

?>
