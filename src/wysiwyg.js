export function loadWysiwyg() {
    return window.STUDIP.loadChunk('wysiwyg').then((exports) => ({
        install: (app) => {
            const ClassicEditor = typeof exports == 'object' ? exports.ClassicEditor : exports;
            app.provide('ClassicEditor', ClassicEditor);
        },
    }));
}
