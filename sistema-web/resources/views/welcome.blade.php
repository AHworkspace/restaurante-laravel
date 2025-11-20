<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Las Brazas - Restaurante</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #2F2B27;
            min-height: 100vh;
            position: relative;
            background: linear-gradient(135deg, #F5F1EC 0%, #E7DED3 60%, #D9C8BB 100%);
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(63, 59, 58, 0.65), rgba(47, 43, 39, 0.75)),
                        url('/images/imagen de portada.jpg') no-repeat center center/cover;
            z-index: -1;
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            text-align: center;
            padding: 0 80px 100px 80px;
            position: relative;
            z-index: 1;
            flex-direction: column;
        }

        .hero-content {
            max-width: 800px;
            padding: 40px;
        }

        .hero-title {
            font-size: 10rem;
            font-weight: 700;
            letter-spacing: 12px;
            margin-bottom: 40px;
            font-family: 'Poppins', sans-serif;
            color: transparent !important;
            -webkit-text-stroke: 2px #FFFFFF;
            text-stroke: 2px #FFFFFF;
            position: relative;
            z-index: 2;
            background: transparent !important;
            text-transform: uppercase;
            text-align: center;
            -webkit-text-fill-color: transparent;
        }

        .hero h1 {
            font-size: 5rem;
            margin-bottom: 20px;
            color: #FDFBF8;
            color: #FDFBF8;
            text-shadow: 2px 2px 4px rgba(122, 92, 88, 0.45);
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(45deg, #6F4E37, #CDBEAC);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
        }

        .hero p {
            font-size: 1.8rem;
            margin-bottom: 40px;
            color: #FDFBF8;
            text-shadow: 1px 1px 2px rgba(38, 35, 32, 0.6);
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 30px;
            justify-content: center;
        }

        .cta-button {
            background: linear-gradient(135deg, #6F4E37, #CDBEAC);
            color: #2F2B27;
            padding: 15px 40px;
            text-decoration: none;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 1.2rem;
            box-shadow: 0 10px 22px rgba(47, 43, 39, 0.25);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 26px rgba(47, 43, 39, 0.35);
            background: linear-gradient(135deg, #5D403D, #B59D90);
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 5rem;
                letter-spacing: 6px;
                -webkit-text-stroke: 1.5px #FFFFFF;
                text-stroke: 1.5px #FFFFFF;
            }

            .hero {
                padding: 40px 30px 100px 50px;
                justify-content: flex-start;
            }

            .hero h1 {
                font-size: 3rem;
            }

            .hero p {
                font-size: 1.4rem;
            }

            .cta-buttons {
                flex-direction: row;
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <main class="hero">
        <h1 class="hero-title">LAS BRAZAS</h1>
        <div class="hero-content">
            <div class="cta-buttons">
                @if(Auth::check())
                    <a href="{{ route('home') }}" class="cta-button">Home</a>
                @else
                    <a href="{{ route('login') }}" class="cta-button">Ingresar</a>
                @endif
                {{-- <a href="{{ route('register') }}" class="cta-button">Registrarse</a> --}}
            </div>
        </div>
    </main>
</body>

</html>
