<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BioTrack QR | Referencia de API</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8b5cf6;
            --secondary: #06b6d4;
            --bg: #0f172a;
            --sidebar-bg: #1e293b;
            --card-bg: #1e293b;
            --text: #f1f5f9;
            --text-dim: #94a3b8;
            --code-bg: #0f172a;
            --get: #10b981;
            --post: #3b82f6;
            --patch: #f59e0b;
            --delete: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        aside {
            background-color: var(--sidebar-bg);
            padding: 2rem;
            position: sticky;
            top: 0;
            height: 100vh;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            overflow-y: auto;
        }

        .logo-area {
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-link { text-decoration: none; }

        .logo-text {
            font-weight: 800;
            font-size: 1.2rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            cursor: pointer;
        }

        .nav-group { margin-bottom: 2rem; }
        .nav-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
            margin-bottom: 1rem;
            display: block;
        }

        .nav-link {
            display: block;
            color: var(--text-dim);
            text-decoration: none;
            padding: 0.5rem 0;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .nav-link:hover, .nav-link.active { color: var(--text); }

        /* Main Content */
        main { padding: 4rem; overflow-y: auto; }
        .content-area { max-width: 900px; margin: 0 auto; }

        section { margin-bottom: 6rem; scroll-margin-top: 4rem; }

        h1 { font-size: 3rem; font-weight: 800; margin-bottom: 1rem; letter-spacing: -2px; }
        h2 { font-size: 2rem; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 0.5rem; }
        h3 { font-size: 1.4rem; margin: 2rem 0 1rem; color: var(--primary); }

        p { color: var(--text-dim); margin-bottom: 1.5rem; font-size: 1.05rem; }

        /* Endpoints */
        .endpoint {
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .method-tag {
            font-weight: 800;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .method-tag.GET { background: rgba(16, 185, 129, 0.1); color: var(--get); }
        .method-tag.POST { background: rgba(59, 130, 246, 0.1); color: var(--post); }
        .method-tag.PATCH { background: rgba(245, 158, 11, 0.1); color: var(--patch); }
        .method-tag.DELETE { background: rgba(239, 68, 68, 0.1); color: var(--delete); }

        .endpoint-path { font-family: 'Fira Code', monospace; font-size: 1.1rem; color: var(--text); }

        /* Tabs / Code */
        .code-tabs {
            margin-top: 1.5rem;
            background: var(--code-bg);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
        }

        .tab-headers {
            display: flex;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            font-size: 0.8rem;
            color: var(--text-dim);
            cursor: pointer;
            border: none;
            background: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .tab-btn.active { color: var(--primary); background: rgba(139, 92, 246, 0.05); box-shadow: inset 0 -2px 0 var(--primary); }

        .code-content { display: none; }
        .code-content.active { display: block; }

        pre { padding: 1.5rem; font-family: 'Fira Code', monospace; font-size: 0.9rem; color: #a5d6ff; line-height: 1.6; }

        @media (max-width: 1024px) {
            body { grid-template-columns: 1fr; }
            aside { display: none; }
            main { padding: 2rem; }
        }
    </style>
</head>
<body>
    <aside>
        <div class="logo-area">
            <a href="/" class="logo-link"><span class="logo-text">BioTrack QR</span></a>
        </div>
        
        <div class="nav-group">
            <span class="nav-label">Introducción</span>
            <a href="#auth" class="nav-link">Autenticación</a>
            <a href="#formats" class="nav-link">Respuestas</a>
        </div>

        <div class="nav-group">
            <span class="nav-label">Endpoints</span>
            <a href="#users" class="nav-link">Usuarios</a>
            <a href="#attendance" class="nav-link">Asistencia</a>
            <a href="#justifications" class="nav-link">Justificaciones</a>
            <a href="#roles" class="nav-link">Roles</a>
        </div>

        <div class="nav-group">
            <span class="nav-label">Recursos</span>
            <a href="/" class="nav-link">Volver al Home</a>
        </aside>

    <main>
        <div class="content-area">
            <section id="intro">
                <h1>Referencia de API</h1>
                <p>Bienvenido a la documentación de BioTrack QR. Esta guía detalla cómo interactuar con nuestra API REST para gestionar asistencia, personal y reportes en tiempo real.</p>
            </section>

            <section id="auth">
                <h2>🔐 Autenticación</h2>
                <p>Nuestra API utiliza tokens de portador (Bearer Tokens) gestionados por Laravel Sanctum. Todos los requests (excepto login) deben incluir el header: <code>Authorization: Bearer {tu_token}</code>.</p>
                
                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method-tag POST">POST</span>
                        <span class="endpoint-path">/api/login</span>
                    </div>
                    <p>Obtén un token de acceso proporcionando credenciales válidas.</p>
                    <div class="code-tabs">
                        <div class="tab-headers">
                            <div class="tab-btn active" data-tab="curl">CURL</div>
                            <div class="tab-btn" data-tab="js">Javascript</div>
                            <div class="tab-btn" data-tab="json">Response JSON</div>
                        </div>
                        <div class="code-block">
                            <div id="curl" class="code-content active">
<pre><code>curl -X POST https://api.biotrack.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@test.com", "password": "password"}'</code></pre>
                            </div>
                            <div id="js" class="code-content">
<pre><code>const response = await fetch('https://api.biotrack.com/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'admin@test.com',
        password: 'password'
    })
});

const data = await response.json();
console.log(data.token);</code></pre>
                            </div>
                            <div id="json" class="code-content">
<pre><code>{
  "message": "Login exitoso",
  "status": 200,
  "data": {
    "user": {
      "id": 1,
      "name": "Administrador",
      "email": "admin@test.com",
      "role": "Admin"
    },
    "token": "1|p3r5on4l_4cc355_7ok3n_h3r3..."
  }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="users">
                <h2>👤 Usuarios</h2>
                <p>Administra los perfiles de los empleados y sus datos base.</p>

                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method-tag GET">GET</span>
                        <span class="endpoint-path">/api/users</span>
                    </div>
                    <p>Retorna una lista completa de usuarios con sus perfiles de imagen procesados.</p>
                </div>

                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method-tag POST">POST</span>
                        <span class="endpoint-path">/api/users</span>
                    </div>
                    <p>Registra un nuevo empleado. Requiere multipart/form-data para la imagen.</p>
                </div>
            </section>

            <section id="attendance">
                <h2>⏱️ Asistencia</h2>
                <p>Capa de registro para escaneos de código QR.</p>

                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method-tag POST">POST</span>
                        <span class="endpoint-path">/api/users/{id}/attendance</span>
                    </div>
                    <p>Registra automáticamente un ingreso o egreso dependiendo del estado actual del usuario y el evento activo.</p>
                    <div class="code-tabs">
                        <div class="tab-headers">
                            <div class="tab-btn active" data-tab="fetch">Fetch</div>
                        </div>
                        <div class="code-block">
                            <div id="fetch" class="code-content active">
<pre><code>fetch('/api/users/qr-xY7z9/attendance', {
  method: 'POST',
  headers: { 
    'Authorization': 'Bearer 1|token...',
    'Accept': 'application/json'
  }
}).then(res => res.json());</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="justifications">
                <h2>📝 Justificaciones</h2>
                <p>Módulo para la aprobación de ausencias y carga de evidencia médica o personal.</p>

                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method-tag PATCH">PATCH</span>
                        <span class="endpoint-path">/api/justifications/{id}/status</span>
                    </div>
                    <p>Cambia el estado de una justificación a 'Aprobado' o 'Rechazado'.</p>
                </div>
            </section>

            <section id="formats">
                <h2>📊 Formato de Respuesta General</h2>
                <p>Mantenemos un esquema de respuesta predecible para facilitar la integración.</p>
                <div class="code-tabs">
                    <pre><code>{
  "message": "Operación exitosa",
  "statusCode": 200,
  "data": {
    "id": 1,
    "name": "Moises Corea",
    "status": "Activo"
  }
}</code></pre>
                </div>
            </section>

            <footer>
                BioTrack Pulse Track API &copy; 2026. Documentación técnica para integraciones empresariales.
            </footer>
        </div>
    </main>

    <script>
        // Tab system logic
        document.querySelectorAll('.code-tabs').forEach(tabContainer => {
            const buttons = tabContainer.querySelectorAll('.tab-btn');
            const contents = tabContainer.querySelectorAll('.code-content');

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const tabId = btn.getAttribute('data-tab');

                    // Reset all
                    buttons.forEach(b => b.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));

                    // Set active
                    btn.classList.add('active');
                    const target = tabContainer.querySelector(`#${tabId}`);
                    if(target) target.classList.add('active');
                });
            });
        });

        // Smooth scroll for nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if(href.startsWith('#')) {
                    // Let the browser handle simple anchors or add GSAP/Scroll logic if needed
                }
            });
        });
    </script>
</body>
</html>
