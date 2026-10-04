<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#14130F">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%2314130F'/%3E%3Cpath d='M8 10h16v4a2 2 0 000 4v4H8v-4a2 2 0 000-4z' fill='%23FF5A1F'/%3E%3C/svg%3E">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700;12..96,800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    display: ['"Bricolage Grotesque"', '"Plus Jakarta Sans"', 'ui-sans-serif', 'sans-serif'],
                    mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
                },
                borderRadius: { md: '0.375rem', lg: '0.5rem', xl: '0.625rem', '2xl': '0.75rem', '3xl': '0.875rem' },
                colors: {
                    paper: '#F6F3EC',
                    ink: { DEFAULT: '#14130F' },
                    flame: { 50: '#FFF1EB', 100: '#FFDFD1', 200: '#FFBFA3', 300: '#FF9A70', 400: '#FF7842', 500: '#FF5A1F', 600: '#E8440C', 700: '#BF3508' },
                    // Legacy "indigo" classes now resolve to the ink palette so every older view follows the new brand.
                    indigo: { 50: '#F3F1EC', 100: '#E8E5DC', 200: '#D3CFC2', 300: '#B1AC9C', 400: '#8B8677', 500: '#5A564B', 600: '#1C1A16', 700: '#100F0C', 800: '#0A0907', 900: '#050403' },
                    gray: { 50: '#FAF8F4', 100: '#F2EFE8', 200: '#E6E2D8', 300: '#D0CCBF', 400: '#A39E90', 500: '#78736A', 600: '#57534B', 700: '#3F3B35', 800: '#27241F', 900: '#14130F' },
                },
            },
        },
    };
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    html { -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
    body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; background: #F6F3EC; color: #14130F; }
    h1, h2, h3 { font-family: 'Bricolage Grotesque', 'Plus Jakarta Sans', ui-sans-serif, sans-serif; letter-spacing: -0.015em; }
    [x-cloak] { display: none !important; }
    ::selection { background: #FF5A1F; color: #fff; }

    .eyebrow { font-size: 0.6875rem; line-height: 1; letter-spacing: 0.14em; text-transform: uppercase; font-weight: 600; color: #78736A; }

    /* Ticket perforation: dashed line with punched-out circles on both edges */
    .perf { position: relative; border-top: 1.5px dashed #D0CCBF; }
    .perf::before, .perf::after { content: ""; position: absolute; top: -10px; width: 20px; height: 20px; border-radius: 9999px; background: #F6F3EC; }
    .perf::before { left: -11px; }
    .perf::after { right: -11px; }
    .perf-ink { border-top-color: rgba(255, 255, 255, 0.25); }

    /* Wristband stripes used as image fallback */
    .stripes { background-size: auto; }

    /* SweetAlert2 – warm, compact, not stiff */
    .swal2-popup { font-family: 'Plus Jakarta Sans', sans-serif !important; border-radius: 14px !important; border: 1px solid #E6E2D8; box-shadow: 0 24px 60px -24px rgba(20, 19, 15, 0.45) !important; padding: 1.5rem 1.5rem 1.25rem !important; }
    .swal2-title { font-family: 'Bricolage Grotesque', sans-serif !important; font-size: 1.25rem !important; font-weight: 700 !important; color: #14130F !important; }
    .swal2-html-container { font-size: 0.9rem !important; color: #57534B !important; line-height: 1.55; }
    .swal2-styled { border-radius: 8px !important; font-weight: 600 !important; font-size: 0.875rem !important; box-shadow: none !important; padding: 0.6rem 1.1rem !important; }
    .swal2-styled:focus { box-shadow: 0 0 0 3px rgba(255, 90, 31, 0.3) !important; }
    .swal2-toast { border-radius: 10px !important; border: 1px solid #E6E2D8; padding: 0.7rem 1rem !important; }
    .swal2-toast .swal2-title { font-size: 0.875rem !important; font-family: 'Plus Jakarta Sans', sans-serif !important; font-weight: 600 !important; }

    /* Form focus ring follows the brand accent */
    input:focus, select:focus, textarea:focus { outline: none; }
</style>
