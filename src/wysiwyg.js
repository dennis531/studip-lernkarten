export function loadWysiwyg() {
    return window.STUDIP.loadChunk('wysiwyg').then((ClassicEditor) => ({
        install: (app) => {
            app.provide('ClassicEditor', ClassicEditor);
        },
    }));
}
