/**
 * Réduit une capture d'écran avant l'envoi (1920 px maximum).
 * WebP garde le texte net (lisibilité des messages d'erreur) ; JPEG de qualité élevée en secours.
 */
export function compressImage(file, max = 1920) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const src = URL.createObjectURL(file);
        img.onload = () => {
            URL.revokeObjectURL(src);
            const scale = Math.min(1, max / Math.max(img.width, img.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(img.width * scale);
            canvas.height = Math.round(img.height * scale);
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff'; // fond blanc pour les PNG transparents
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(
                (webp) => {
                    if (webp && webp.type === 'image/webp') {
                        return resolve(new File([webp], 'capture.webp', { type: 'image/webp' }));
                    }
                    canvas.toBlob(
                        (jpg) => (jpg ? resolve(new File([jpg], 'capture.jpg', { type: 'image/jpeg' })) : reject()),
                        'image/jpeg',
                        0.9,
                    );
                },
                'image/webp',
                0.9,
            );
        };
        img.onerror = () => {
            URL.revokeObjectURL(src);
            reject();
        };
        img.src = src;
    });
}

/** Extrait les images d'un collage (Ctrl+V) ou d'un glisser-déposer. */
export function imagesFrom(dataTransfer) {
    return [...(dataTransfer?.files ?? [])].filter((f) => f.type.startsWith('image/'));
}
