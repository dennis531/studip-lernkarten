import Kitsu from 'kitsu';

const absoluteUriStudip = new URL(window.STUDIP.ABSOLUTE_URI_STUDIP);
const baseURL = `${absoluteUriStudip.pathname}jsonapi.php/v1/`;
const api = new Kitsu({ baseURL, camelCaseTypes: false, pluralize: false, resourceCase: 'kebab' });

export { api };
