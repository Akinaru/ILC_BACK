{{-- resources/views/welcome.blade.php --}}
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    // Récupère toutes les routes puis filtre celles de l'API
    $routes = collect(Route::getRoutes())
        ->filter(function ($route) {
            // Certaines versions stockent le préfixe dans action['prefix'], sinon on vérifie l'URI
            $uri = $route->uri();
            $prefix = $route->action['prefix'] ?? null;
            return ($prefix === 'api') || Str::startsWith($uri, 'api/');
        })
        ->map(function ($route) {
            $methods = array_values(array_diff($route->methods(), ['HEAD']));
            $uri = $route->uri();
            $action = $route->getActionName();

            // Récupération complète des middlewares (incluant ceux de groupe)
            $middlewares = method_exists($route, 'gatherMiddleware')
                ? $route->gatherMiddleware()
                : ($route->middleware() ?? []);

            // Détection du niveau de protection
            $hasAuth = collect($middlewares)->contains(function ($m) {
                // couvre 'auth', 'auth:sanctum', 'auth:api', etc.
                return Str::startsWith($m, 'auth');
            });

            $isAdmin = collect($middlewares)->contains(function ($m) {
                // couvre 'role:admin' (Spatie ou middleware custom)
                return Str::startsWith($m, 'role:') && Str::contains($m, 'admin');
            });

            $protection = 'Publique';
            if ($hasAuth && $isAdmin) {
                $protection = 'Admin';
            } elseif ($hasAuth) {
                $protection = 'Protégée';
            }

            // Mise en forme lisible de l'action
            if ($action === 'Closure') {
                $actionReadable = 'Closure';
            } else {
                // Exemple: App\Http\Controllers\ArticleController@index -> ArticleController@index
                $actionReadable = Str::after($action, 'App\\Http\\Controllers\\');
            }

            // Tri des middlewares pour lisibilité
            $middlewares = collect($middlewares)
                ->unique()
                ->values()
                ->all();

            return [
                'methods'     => $methods,
                'uri'         => $uri,
                'name'        => $route->getName(),
                'action'      => $actionReadable,
                'middlewares' => $middlewares,
                'protection'  => $protection,
            ];
        })
        // Optionnel: trier par URI puis par méthode
        ->sortBy([
            fn($a, $b) => Str::of($a['uri'])->lower()->__toString() <=> Str::of($b['uri'])->lower()->__toString(),
            fn($a, $b) => implode(',', $a['methods']) <=> implode(',', $b['methods']),
        ])
        ->values();
@endphp

<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Routes — Laravel</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script>
        // Filtre simple côté client
        function filterRoutes() {
            const q = (document.getElementById('q').value || '').toLowerCase();
            const rows = document.querySelectorAll('tbody tr[data-row]');
            rows.forEach(tr => {
                const blob = tr.getAttribute('data-search');
                tr.style.display = blob.includes(q) ? '' : 'none';
            });
        }
    </script>
    <style>
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial; color: #111827; }
        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
        .badge { display:inline-flex; align-items:center; gap: .35rem; padding: .15rem .5rem; border-radius: .375rem; font-size: .75rem; font-weight: 600; }
        .badge.gray { background: #e5e7eb; color:#111827; }
        .badge.blue { background: #dbeafe; color:#1e40af; }
        .badge.amber { background: #fef3c7; color:#92400e; }
        .badge.red { background: #fee2e2; color:#991b1b; }
        .chip { display:inline-block; padding:.15rem .5rem; margin:.1rem; font-size:.72rem; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:.375rem; }
        .card { background:white; border:1px solid #e5e7eb; border-radius:.75rem; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
        .table { width:100%; border-collapse: collapse; }
        .table thead th { font-size:.8rem; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; text-align:left; padding:.75rem 1rem; border-bottom:1px solid #e5e7eb; background:#f9fafb; position:sticky; top:0; }
        .table tbody td { padding:.75rem 1rem; border-bottom:1px solid #f3f4f6; vertical-align: top; }
        .muted { color:#6b7280; }
        .pill { display:inline-block; padding:.15rem .5rem; border-radius:.375rem; background:#eef2ff; color:#3730a3; font-size:.72rem; }
        .search { width:100%; padding:.6rem .8rem; border:1px solid #e5e7eb; border-radius:.5rem; }
        .header { display:flex; align-items:end; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
        .legend { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
        .legend > span { font-size:.8rem; }
        .footer { color:#6b7280; font-size:.85rem; padding: .75rem 1rem; }
        code.kbd { background:#f3f4f6; border:1px solid #e5e7eb; border-bottom-width:2px; padding:.1rem .35rem; border-radius:.35rem; font-size:.75rem; }
    </style>
</head>
<body class="h-full">
    <div class="container">
        <div class="header" style="margin-bottom:1rem;">
            <div>
                <h1 style="font-size:1.5rem; font-weight:700; margin:0;">Catalogue des routes API</h1>
                <p class="muted" style="margin:.25rem 0 0;">Généré depuis <code class="kbd">routes/api.php</code> ({{ $routes->count() }} routes détectées)</p>
            </div>
            <div class="legend">
                <span class="badge gray">Publique</span>
                <span class="badge blue">Protégée</span>
                <span class="badge red">Admin</span>
            </div>
        </div>

        <div class="card" style="overflow:hidden;">
            <div style="padding: .75rem 1rem; border-bottom:1px solid #e5e7eb; background:#fff;">
                <input id="q" class="search" type="search" placeholder="Rechercher (URI, méthode, contrôleur, middleware…)" oninput="filterRoutes()" />
            </div>

            <div style="max-height: 70vh; overflow:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:110px;">Méthode</th>
                            <th>URI</th>
                            <th>Protection</th>
                            <th>Middlewares</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $r)
                            @php
                                $methodsLabel = implode(' | ', $r['methods']);
                                $searchBlob = strtolower(implode(' ', [
                                    $methodsLabel,
                                    $r['uri'],
                                    $r['action'] ?? '',
                                    implode(' ', $r['middlewares'] ?? []),
                                    $r['name'] ?? ''
                                ]));
                                $badgeClass = match ($r['protection']) {
                                    'Admin' => 'red',
                                    'Protégée' => 'blue',
                                    default => 'gray',
                                };
                            @endphp
                            <tr data-row data-search="{{ $searchBlob }}">
                                <td>
                                    @foreach($r['methods'] as $m)
                                        <span class="chip">{{ $m }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                                        /{{ $r['uri'] }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">{{ $r['protection'] }}</span>
                                </td>
                                <td>
                                    @if(!empty($r['middlewares']))
                                        @foreach($r['middlewares'] as $mw)
                                            <span class="pill">{{ $mw }}</span>
                                        @endforeach
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="muted" style="text-align:center; padding:2rem;">Aucune route API trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="footer">
                Astuce : toutes les routes listées sont sous le préfixe <code class="kbd">/api</code>.  
                Les badges « Protégée » détectent les middlewares commençant par <code class="kbd">auth</code> (ex. <code class="kbd">auth:sanctum</code>).  
                Le badge « Admin » est affiché si <code class="kbd">auth*</code> et un <code class="kbd">role:admin</code> sont présents.
            </div>
        </div>
    </div>
</body>
</html>
