class Stars {
    constructor(
        className = "",
        numOfStars = 0,
        styleOptions = {margin: "5px"}
    ) {
        this.isValid = false;
        this.numOfStars = numOfStars;
        this.stars = [];
        this.styleOptions = styleOptions;
        this.starsContainers = document.querySelectorAll(className);

        try {
            if (this.starsContainers) {
                this.className = className;
                this.isValid = true;
            } else {
                this.isValid = false;
                throw new Error(`${className} does not exist.`);
            }
        } catch (e) {
            // console.log(e.message);
        }

        if (this.isValid && this.numOfStars > 0) {
            this.init();
        }
    }

    init() {
        [].forEach.call(this.starsContainers,(container) => {
            this.create(container);
        });
    }

    create(container) {
        const ul = document.createElement("ul");

        this.stars = [];

        for (let i = 0; i < this.numOfStars; i++) {
            this.stars.push({id: i + 1});
        }

        container.addEventListener('mouseleave', (e) => {
            for (const item of e.target.querySelectorAll("a")) {
                item.style.color = "";
                item.innerHTML = "&#9734";
            }
        });

        ul.style.listStyleType = "none";
        ul.style.display = "flex";
        ul.style.padding = "0";
        ul.style.margin = "0";

        const stars = this.stars.map((star) => {
            const li = document.createElement("li");
            const a = document.createElement("a");

            li.style.margin = this.styleOptions.margin;
            a.style.cursor = "pointer";

            a.innerHTML = "&#9734";
            a.id = star.id;

            a.addEventListener("mouseover", (e) => {
                this.setRating(ul, e);
            });

            li.appendChild(a);
            return li;
        });

        const fragment = document.createDocumentFragment();

        for (const star of stars) {
            fragment.appendChild(star);
        }

        ul.appendChild(fragment);
        container.appendChild(ul);
    }

    setRating(ul, e) {
        const listItems = ul.querySelectorAll("li");
        const currentId = Number(e.target.id);

        for (const item of listItems) {
            const a = item.querySelector("a");
            a.style.color = "";
            if (a.id <= currentId) {
                a.innerHTML = "&#9733";
                a.style.color = "gold";
            } else {
                a.style.color = "";
                a.innerHTML = "&#9734";
            }
        }
    }
}