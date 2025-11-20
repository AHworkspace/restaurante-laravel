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
            color: #fff;
            min-height: 100vh;
            position: relative;
            background: #1a1a1a;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.1)),
                        url('/images/fondo.jpg') no-repeat center center/cover;
            z-index: -1;
        }

        .social-links {
            position: fixed;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 20px;
            z-index: 2;
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .social-link {
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s;
            padding: 10px;
            border-radius: 10px;
            background: rgba(255, 77, 77, 0.2);
        }

        .social-link:hover {
            background: rgba(255, 77, 77, 0.4);
            transform: translateX(-5px);
        }

        .social-link i {
            font-size: 1.8rem;
            margin-right: 10px;
        }

        .social-link span {
            font-size: 0.9rem;
            white-space: nowrap;
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s;
        }

        .social-link:hover span {
            opacity: 1;
            transform: translateX(0);
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            text-align: center;
            padding: 0 20px;
            position: relative;
            z-index: 1;
            padding-bottom: 100px;
        }

        .hero-content {
            max-width: 800px;
            padding: 40px;
        }

        .hero h1 {
            font-size: 5rem;
            margin-bottom: 20px;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(255, 77, 77, 0.5);
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(45deg, #ff4d4d, #ff8080);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
        }

        .hero p {
            font-size: 1.8rem;
            margin-bottom: 40px;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 30px;
            justify-content: center;
        }

        .cta-button {
            background: linear-gradient(45deg, #ff4d4d, #ff8080);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(255, 77, 77, 0.4);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 77, 77, 0.6);
            background: linear-gradient(45deg, #ff3333, #ff6666);
        }

        @media (max-width: 768px) {
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

            .social-links {
                position: fixed;
                bottom: 20px;
                right: 50%;
                top: auto;
                transform: translateX(50%);
                flex-direction: row;
                padding: 15px;
            }

            .social-link {
                font-size: 1.5rem;
            }

            .social-link span {
                display: none;
            }
        }
    </style>
</head>

<body>
    <main class="hero">
        <div class="hero-content">
            <div class="cta-buttons">
                <?php if(Auth::check()): ?>
                    <a href="<?php echo e(route('home')); ?>" class="cta-button">Home</a>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="cta-button">Ingresar</a>
                <?php endif; ?>
                
            </div>
        </div>
    </main>

    <div class="social-links">
        <a href="https://www.tiktok.com/@restaurant.las.brazas?_t=ZM-8vFpHU4jidz&_r=1" target="_blank" class="social-link" title="Síguenos en TikTok">
            <i class="fab fa-tiktok"></i>
            <span>Síguenos en TikTok</span>
        </a>
        <a href="https://wa.me/59176396861" target="_blank" class="social-link" title="Pedidos y Reservas">
            <i class="fab fa-whatsapp"></i>
            <span>Pedidos y Reservas: 76396861</span>
        </a>
    </div>
</body>

</html>
<?php /**PATH C:\Users\20\Desktop\OTROS PROGRAMAS\PROYECTO PARA TECNOWEB\proyecto prueba 1\proyecto-tecnoweb_restaurante\sistema-web\resources\views/welcome.blade.php ENDPATH**/ ?>