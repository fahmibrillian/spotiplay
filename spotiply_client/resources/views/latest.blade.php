<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Spotiplay | Home</title>
        {{-- description --}}

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 40px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
            .m-r-20 {
                margin-right: 20px;
            }
            .m-l-20 {
                margin-left: 60px;
            }

            .col-6 {
                width: 50%;
            }

            .row {
                display: flex;
            }

            /* Content margin top 20px */
            .content {
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="position-ref full-height">
            <div class="top-right links">
                @if(session()->get('spotify_access_token') != null)
                    <a href="{{ route('logout') }}">Logout</a>
                @endif
            </div>
            <div class="content">
                <div class="title m-b-md" style="color: #1DB954">
                    Spotiplay
                </div>
                <div class="row">
                    <div class="table col-6">
                        <h3>Latest Tracks</h1>
                        <table class="m-l-20">
                            <thead>
                                <tr>
                                    <th>Track</th>
                                    <th>Artist</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tracks as $track)
                                    <tr>
                                        <td>{{$track['track']['name']}}</td>
                                        <td>
                                            @foreach ($track['track']['artists'] as $artist)
                                                {{$artist['name']}}<br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @php
                                                $minutes = floor($track['track']['duration_ms'] / 60000);
                                                $seconds = floor(($track['track']['duration_ms'] % 60000) / 1000);
                                            @endphp
                                            {{-- add 0 if minutes of second is single digit --}}
                                            {{str_pad($minutes, 2, '0', STR_PAD_LEFT)}}:{{str_pad($seconds, 2, '0', STR_PAD_LEFT)}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table col-6">
                        <h3>Recomendation Tracks</h1>
                        <table class="m-l-20">
                            <thead>
                                <tr>
                                    <th>Track</th>
                                    <th>Recommendation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rec as $key => $tracks)
                                    <tr>
                                        <td>{{$key}}</td>
                                        <td>
                                            @foreach ($tracks as $key => $track)
                                            @if ($key == 5)
                                                @php
                                                    break;
                                                @endphp
                                            @endif
                                                {{$track['name']}}<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table col-6">
                        <h3>Your Characteristic</h1>
                        <div id="chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            var options = {
            fill: {
                opacity: 0.5,
                colors: ['#1DB954']
            },
            stroke: {
                width: 1,
                colors: ['#1DB954']
            },
            markers: {
                enabled: true,
                size: 4,
                colors: ['#1DB954'],
                strokeColor: '#1DB954',
                strokeWidth: 2,
            },
            chart: {
                type: 'radar',
            },
            series: [
                {
                name: "Characteristic",
                data: [
                    @foreach ($chara as $key => $value)
                        {{$value}},
                    @endforeach
                ]
                },
            ],
            labels: ['Danceability', 'Energy', 'Speechiness', 'Acousticness', 'Instrumentalness', 'Liveness'],
            yaxis: {
                show: true,
                min: 0,
                max: 1,
                tickAmount: 5,
                labels: {
                    show: true,
                    formatter: function (val) {
                        return val.toFixed(2);
                    },
                },
            },
            }

            var chart = new ApexCharts(document.querySelector("#chart"), options);

            chart.render();
        </script>
    </body>
</html>
