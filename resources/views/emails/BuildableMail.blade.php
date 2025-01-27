<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Conferma Email</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #e9f0fb; color: #161c3e; margin: 0; padding: 10px 0px 0px; width: 100%;">
   <!-- Footer -->
   @php
       $locale = 'it_IT';

        // Ottieni la data odierna
        $dataOdierna = new DateTime();

        // Formatter per la data in italiano
        $formatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL, // Stile della data (es. FULL, LONG, MEDIUM, SHORT)
            IntlDateFormatter::NONE  // Nessun orario
        );
   @endphp
    <img style="width: 80px; margin: 2rem; filter: drop-shadow( 0 0 15px black); " src="{{config('configurazione.APP_URL') . '/public/favicon.png'}}" alt="">


    <h1 style="color: #04001d; font-size: 28px; padding: 20px;">{{$content_mail['heading']}}</h1>
    
    @if($content_mail['img_1'] !== NULL)   
    <img style="border-radius: 10px; width: 40%; margin-top: 2rem; margin-bottom: 2rem; margin-left: 30%;" src="{{config('configurazione.APP_URL') . '/public/storage/' . $content_mail['img_1']}}" alt="">
    @endif
    
    <div style="margin: 3rem; font-size: 20px; color: rgb(28, 28, 29)" class="corpo">
        @foreach ($content_mail['body'] as $b)
        <p>{{$b}}</p>
        @endforeach
    </div>
    
    @if($content_mail['img_2'] !== NULL)   
        <img style="border-radius: 10px; width: 20%; margin-top: 2rem; margin-bottom: 2rem; margin-left: 40%;" src="{{config('configurazione.APP_URL') . '/public/storage/' . $content_mail['img_2']}}" alt="">
    @endif

    <p style="color: #04001d; font-size: 22px; text-align: center; margin: .6rem 30px">{{$content_mail['ending']}}</p>

    <div class="sender" style="color: #04001d">
        <p style="font-weight: 900; font-size: 18px; margin: 1rem 2rem 0">{{$content_mail['sender']}}</p>
        <p style="font-style: italic; font-size: 16px; margin: 4px 2rem 1rem" class="date">{{$formatter->format($dataOdierna)}}</p>
    </div>

    <footer style=" margin: 50px 0 0; background-color: black; color: white; padding: 10px 20px; text-align: center; font-size: 12px;">
        <h5 style="font-size: 16px; margin: 5px 0 8px;">Seguici sui social</h5>
        <div style="padding: 0 0 20px "> 
            <a style="color: white; text-decoration: none; margin: 0 auto;" href="https://www.facebook.com/profile.php?id=61558817374447">
                Facebook: <span style="color: white; font-weight: 900"> Future plus</span>
            </a>
            <a style="color: white; text-decoration: none; margin: 0 auto;" href="https://www.instagram.com/future.plus_/?hl=it">
                Instagram: <span style="color: white; font-weight: 900"> @future.plus_</span>
            </a>
        </div>
        <p style="font-size: 12px; font-family: monospace; line-height: 1.5; margin: 10px 5px;">&copy; 2024 {{ config('configurazione.APP_NAME') }}. Tutti i diritti riservati.</p>
        <p style="font-size: 15px; line-height: 1.5; margin: 5px;" > Powered by <a style="color: white; text-decoration: none; font-weight:900;" href="https://future-plus.it">Future +</a></p>
    </footer>
</body>
</html>

