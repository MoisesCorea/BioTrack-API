<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BioTrack QR - Pulse Track API</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

    <!-- EmailJS SDK -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>

    <style>
        :root {
            --primary: #8b5cf6;
            --secondary: #06b6d4;
            --bg: #0a0a0f;
            --card-bg: rgba(255, 255, 255, 0.03);
            --text: #f8fafc;
            --text-dim: #94a3b8;
            --error: #ef4444;
            --success: #10b981;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(139, 92, 246, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(6, 182, 212, 0.1) 0%, transparent 40%);
        }

        header {
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav a {
            color: var(--text-dim);
            text-decoration: none;
            margin-left: 2rem;
            font-weight: 600;
            transition: color 0.3s;
        }

        nav a:hover { color: var(--primary); }

        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
            text-align: center;
        }

        .hero { margin-bottom: 6rem; }
        .hero h1 {
            font-size: clamp(3rem, 10vw, 5rem);
            font-weight: 800;
            letter-spacing: -3px;
            line-height: 1;
            margin-bottom: 1.5rem;
        }
        .hero p {
            color: var(--text-dim);
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto 3rem;
        }

        .badge {
            background: rgba(139, 92, 246, 0.1);
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            display: inline-block;
            border: 1px solid rgba(139, 92, 246, 0.2);
        }

        .cta-btn {
            background: var(--primary);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 14px;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 10px 30px -10px var(--primary);
            transition: transform 0.3s;
            display: inline-block;
        }
        .cta-btn:hover { transform: translateY(-3px); }

        /* Features */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 8rem;
        }
        .card {
            background: var(--card-bg);
            padding: 3rem;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: left;
            backdrop-filter: blur(20px);
        }
        .card h3 { margin-bottom: 1rem; font-size: 1.5rem; }
        .card p { color: var(--text-dim); }

        /* Contact Section */
        #contact {
            margin-top: 4rem;
            padding: 6rem 2rem;
            background: radial-gradient(circle at center, rgba(139, 92, 246, 0.05) 0%, transparent 70%);
            width: 100vw;
            margin-left: calc(-50vw + 50%);
        }

        .contact-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .contact-container h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; }
        .contact-container p { color: var(--text-dim); margin-bottom: 3rem; }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid rgba(255, 255, 255, 0.07);
            padding: 1.2rem;
            border-radius: 14px;
            color: white;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 20px -10px var(--primary);
        }

        .form-input.invalid { border-color: var(--error); }
        .form-input.valid { border-color: var(--success); }

        .submit-btn {
            width: 100%;
            background: var(--text);
            color: var(--bg);
            padding: 1.2rem;
            border: none;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .submit-btn:hover { background: var(--primary); color: white; }

        .status-msg {
            margin-top: 1.5rem;
            font-weight: 600;
            display: none;
        }

        .status-msg.success { color: var(--success); display: block; }
        .status-msg.error { color: var(--error); display: block; }

        footer {
            padding: 4rem 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-dim);
            font-size: 0.9rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        @media (max-width: 768px) {
            header { padding: 1rem; }
            nav { display: none; }
            footer { flex-direction: column; gap: 1rem; text-align: center; }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">BIOTRACK QR</div>
        <nav>
            <a href="/">Inicio</a>
            <a href="/docs">Documentación</a>
            <a href="#contact">Contacto</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <span class="badge">SISTEMA BIO-MÉTRICO v2.1</span>
            <h1>BioTrack QR: El Pulso de tu Empresa</h1>
            <p>Ecosistema digital diseñado para el control de asistencia inteligente. Seguridad, rapidez y reportes avanzados en una sola API.</p>
            <a href="/docs" class="cta-btn">Empezar a Integrar</a>
        </section>

        <section class="features">
            <div class="card">
                <h3>QR Dinámico</h3>
                <p>Generación y validación instantánea de marcas de tiempo vinculadas a la identidad única de cada colaborador.</p>
            </div>
            <div class="card">
                <h3>RBAC Avanzado</h3>
                <p>Gestión de roles y permisos granulares con Spatie, asegurando que cada usuario acceda solo a lo necesario.</p>
            </div>
            <div class="card">
                <h3>Justificaciones</h3>
                <p>Control automatizado de permisos e incidencias con respaldo documental integrado en la base de datos.</p>
            </div>
        </section>

        <section id="contact">
            <div class="contact-container">
                <h2>¿Necesitas Soporte?</h2>
                <p>¿Tienes dudas con la integración o necesitas una licencia personalizada? Escríbenos.</p>
                
                <form id="contactForm">
                    <div class="form-group">
                        <input type="text" name="user_name" id="name" placeholder="Tu Nombre" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="user_email" id="email" placeholder="Email Corporativo" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" id="message" placeholder="¿En qué podemos ayudarte?" class="form-input" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn" id="submitBtn">Enviar Mensaje</button>
                    <div id="statusMsg" class="status-msg"></div>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div>&copy; 2024 BioTrack QR - Pulse Track. Todos los derechos reservados.</div>
        <div>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</div>
    </footer>

    <script>
        const form = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const statusMsg = document.getElementById('statusMsg');

        // Inicializar EmailJS con tu Public Key
        emailjs.init("Cjacs1R1un-bRpBYB");

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            
        // Cambiar estado del botón
        const originalBtnText = submitBtn.textContent;
        submitBtn.textContent = 'Enviando...';
        submitBtn.disabled = true;

        // Estos IDs deben ser reemplazados por los tuyos de EmailJS
        const serviceID = 'service_iv3gstt';
        const templateID = 'template_h4kefv6'; // Reemplazar con tu Template ID

        emailjs.sendForm(serviceID, templateID, form)
            .then(() => {
                submitBtn.textContent = '¡Enviado!';
                statusMsg.textContent = 'Tu mensaje ha sido enviado con éxito. Me pondré en contacto pronto.';
                statusMsg.className = 'status-msg success';
                form.reset();
                
                // Resetear botón después de 3 segundos
                setTimeout(() => {
                    submitBtn.textContent = originalBtnText;
                    submitBtn.disabled = false;
                    statusMsg.className = 'status-msg'; // Ocultar mensaje
                }, 3000);
            }, (err) => {
                submitBtn.textContent = 'Error';
                submitBtn.disabled = false;
                statusMsg.textContent = 'Hubo un error al enviar el mensaje. Por favor, intenta de nuevo.';
                statusMsg.className = 'status-msg error';
                console.error('EmailJS Error:', err);
            });
        });

        // Real-time validation cues
        form.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', () => {
                if (input.value) {
                    input.classList.add('valid');
                    input.classList.remove('invalid');
                } else {
                    input.classList.remove('valid');
                    input.classList.add('invalid');
                }
            });
        });
    </script>
</body>
</html>
