import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        user: String
    }

    async connect() {
        let uuid = window.location.href.split("/").at(-1);

        const url = new URL('http://127.0.0.1:3000/.well-known/mercure');
        url.searchParams.append('topic', 'https://postus.fr/conversation/' + uuid);

        const eventSource = new EventSource(url);

        eventSource.onmessage = (e) => {
            let username = JSON.parse(e.data).username;
            let message = JSON.parse(e.data).message;

            const htmlEntities = (str) => {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            if (this["userValue"] === username) {
                this.element.innerHTML += `<div>[<b class="text-danger">${htmlEntities(username)}</b>] => ${htmlEntities(message)}</div>`;
            } else {
                this.element.innerHTML += `<div>[${htmlEntities(username)}] => ${htmlEntities(message)}</div>`;
            }

            this.element.scroll(0, this.element.scrollHeight);
        }
    }
}
