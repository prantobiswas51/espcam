<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" x-data="cameras()">
        <template x-for="cam in cams" :key="cam.id">
            <div class="p-4 border rounded">
                <h2 class="font-semibold mb-2" x-text="`Camera ${cam.id}`"></h2>
                <div class="aspect-video bg-gray-100 flex items-center justify-center overflow-hidden">
                    <img :src="cam.srcWithBust" alt="" class="w-full h-full object-contain" x-show="cam.src">
                    <span x-show="!cam.src">No frame yet…</span>
                </div>
                <div class="text-sm text-gray-500 mt-2" x-text="cam.at ? `Updated: ${cam.at}` : ''"></div>
            </div>
        </template>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
        Alpine.data('cameras', () => ({
            cams: [
            { id: 'cam1', src: null, at: null, get srcWithBust(){ return this.src ? (this.src + `?cb=${Date.now()}`) : null } },
            { id: 'cam2', src: null, at: null, get srcWithBust(){ return this.src ? (this.src + `?cb=${Date.now()}`) : null } },
            { id: 'cam3', src: null, at: null, get srcWithBust(){ return this.src ? (this.src + `?cb=${Date.now()}`) : null } },
            ],
            async fetchLatest(cam) {
            try {
                const res = await fetch(`/latest-frame/${cam.id}`, { cache: 'no-store' });
                if (res.ok) {
                const { url, at } = await res.json();
                if (url && url !== cam.src) {
                    cam.src = url;
                    cam.at = at;
                } else {
                    // still update busting so the browser revalidates
                    cam.at = at || cam.at;
                }
                }
            } catch(e) { /* ignore for now */ }
            },
            init() {
            // initial fetch
            this.cams.forEach(c => this.fetchLatest(c));
            // poll each second
            setInterval(() => this.cams.forEach(c => this.fetchLatest(c)), 1000);
            }
        }))
        })
    </script>
</body>

</html>