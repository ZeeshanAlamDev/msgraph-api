<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin</title>
    <link rel="stylesheet" href="{{asset('/assets/css/style.css')}}" alt="logout">
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&amp;display=swap" rel="stylesheet">
</head>

<body class="antialiased">
    <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                
                <div class="grid message">
                    <div class="m-8 info">
                        <div>
                        @if (!empty(Session::get('admin_message')))
                            <pre class="message-coinatiner" id="message-coinatiner">
                                {{Session::get('admin_message')}}
                            </pre>
                        @endif
                        </div>
                    </div>                  
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2">
                        <div class="p-6">
                            <div class="m-8">
                                <div class="text-gray-600 dark:text-gray-400 text-md mt-a">
                                    Login with your passcode to access the admin-only options on this page. After login, the admin can invoke the API script by clicking the 'Run API Script Now' button.
                                </div>
                                <div class="mt-6">
                                    @if (Auth::check())
                                        <div class="form api">
                                            <div class="button__field">
                                                @if ($authorized)
                                                    <a href="{{url('admin/api-call')}}">
                                                        <button>Run API Script Now</button>
                                                    </a>
                                                @else
                                                    <button class="disabled-btn">API is not Connected</button>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <form method="post" action="/login" class="button api">
                                            @csrf
                                            <div class="form__field">
                                                <label for="passcode"><svg class="icon">
                                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#lock"></use>
                                                    </svg><span class="hidden">Passcode</span></label>
                                                <input id="passcode" type="password" name="password" class="form__input" placeholder="Passcode" required="">
                                            </div>
                                            <div class="form__field">
                                                <input type="submit" value="Login">
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if (Auth::check())
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l mt-a">
                            <div class="m-8">
                                <div class="text-gray-600 dark:text-gray-400 text-md">
                                    @if ($authorized)
                                        You have granted permission to this application to use azure API to get appointments and send messages in the MS Teams channel. You can delete authorization data to remove permission. 
                                    @else
                                        Grant permission to this application by clicking the 'Authorize' button before manually invoking get appointments, create contact and send message API scripts.
                                    @endif
                                </div>
                                <div class="mt-6">
                                    <div class="form api">
                                        <div class="button__field">
                                            @if ($authorized)
                                                <a href="{{url('admin/auth-delete')}}">
                                                    <button class="red-btn">Delete Auth Data</button>
                                                </a>
                                            @else
                                                <a href="{{$endpoint}}">
                                                    <button>Authorize</button>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                            <div class="md:border-l mt-a bg-img" style="background-image: url({{ asset('/assets/images/img.jpg') }})">
                                
                            </div>
                        @endif
                    </div>
                </div>
                @if (Auth::check())
                    <div class="bottom-links">
                        <img src="{{url('/assets/images/logout.svg')}}" alt="logout">
                        <a class="logout" href="{{url('/logout')}}">Logout</a>
                    </div>
                @endif
            </div>
        </div>

    <svg xmlns="http://www.w3.org/2000/svg" class="icons">
        <symbol id="arrow-right" viewBox="0 0 1792 1792">
            <path d="M1600 960q0 54-37 91l-651 651q-39 37-91 37-51 0-90-37l-75-75q-38-38-38-91t38-91l293-293H245q-52 0-84.5-37.5T128 1024V896q0-53 32.5-90.5T245 768h704L656 474q-38-36-38-90t38-90l75-75q38-38 90-38 53 0 91 38l651 651q37 35 37 90z"></path>
        </symbol>
        <symbol id="lock" viewBox="0 0 1792 1792">
            <path d="M640 768h512V576q0-106-75-181t-181-75-181 75-75 181v192zm832 96v576q0 40-28 68t-68 28H416q-40 0-68-28t-28-68V864q0-40 28-68t68-28h32V576q0-184 132-316t316-132 316 132 132 316v192h32q40 0 68 28t28 68z"></path>
        </symbol>
        <symbol id="user" viewBox="0 0 1792 1792">
            <path d="M1600 1405q0 120-73 189.5t-194 69.5H459q-121 0-194-69.5T192 1405q0-53 3.5-103.5t14-109T236 1084t43-97.5 62-81 85.5-53.5T538 832q9 0 42 21.5t74.5 48 108 48T896 971t133.5-21.5 108-48 74.5-48 42-21.5q61 0 111.5 20t85.5 53.5 62 81 43 97.5 26.5 108.5 14 109 3.5 103.5zm-320-893q0 159-112.5 271.5T896 896 624.5 783.5 512 512t112.5-271.5T896 128t271.5 112.5T1280 512z"></path>
        </symbol>
    </svg>

    <script>
        const mgsEl = document.getElementById('message-coinatiner') || false;

        if (mgsEl) {
            setTimeout( () => {
                mgsEl.remove();
            }, 10000);
        }
    </script>
</body>

</html>