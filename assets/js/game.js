let game_area = document.getElementById('game-area');
let score_display = document.getElementById('score-display');
let accuracy_display = document.getElementById('accuracy');

let score = 0;
let totalBubbles = 0;  // total de bulles créées
let poppedBubbles = 0; // bulles éclatées

class Bubble {
    constructor(x, y, radius, speed, color, value) {
        this.x = x;
        this.y = y;
        this.radius = radius;
        this.speed = speed;
        this.color = color;
        this.value = value; // score de la bulle

        this.element = document.createElement('div');
        this.element.className = 'bubble';
        this.element.style.width = this.radius * 2 + 'px';
        this.element.style.height = this.radius * 2 + 'px';
        this.element.style.backgroundColor = this.color;
        this.element.style.position = 'absolute';
        this.element.style.left = this.x - this.radius + 'px';
        this.element.style.top = this.y - this.radius + 'px';
        this.element.style.borderRadius = '50%';
        this.element.style.cursor = 'pointer';
        this.element.style.display = 'flex';
        this.element.style.justifyContent = 'center';
        this.element.style.alignItems = 'center';
        game_area.appendChild(this.element);

        // Inner bubble (gradient blanc-transparent)
        let inner = document.createElement('div');
        inner.className = 'inner-bubble';
        inner.style.width = this.radius + 'px';
        inner.style.height = this.radius + 'px';
        inner.style.borderRadius = '50%';
        inner.style.position = 'absolute';
        inner.style.left = '0';
        inner.style.top = '0';
        inner.style.background = 'radial-gradient(circle at top left, rgba(255,255,255,0.8), transparent)';
        this.element.appendChild(inner);

        this.element.addEventListener('click', () => this.pop());
    }

    pop() {
        // Animation
        this.element.style.transition = '0.2s';
        this.element.style.transform = 'scale(1.5)';
        this.element.style.opacity = '0';

        setTimeout(() => this.element.remove(), 200);

        // Score
        score += this.value;
        poppedBubbles++;
        updateScore();
        updateAccuracy();

        // Score popup
        let popup = document.createElement('div');
        popup.className = 'score-popup';
        popup.textContent = (this.value > 0 ? "+" : "") + this.value;
        popup.style.left = this.x + 'px';
        popup.style.top = this.y + 'px';
        game_area.appendChild(popup);

        setTimeout(() => popup.remove(), 500);
    }

    move() {
        this.y -= this.speed;
        this.element.style.top = this.y - this.radius + 'px';
    }
}

function addBubble() {
    totalBubbles++;
    updateAccuracy();

    const x = Math.random() * (game_area.clientWidth - 100) + 50;
    const y = game_area.clientHeight - 50;
    const radius = Math.random() * 30 + 20;
    const speed = Math.random() * 2 + 1;

    // Score basé sur la taille : plus petite bulle = plus gros score
    let value = Math.round((50 - radius) / 5); 
    // 10% de chance d'être une bulle noire qui réduit le score
    const isBlack = Math.random() < 0.1;
    const color = isBlack ? '#000000' : '#' + Math.floor(Math.random() * 16777215).toString(16);
    if (isBlack) value = -5; // bulle noire

    const bubble = new Bubble(x, y, radius, speed, color, value);

    const interval = setInterval(() => {
        bubble.move();
        if (bubble.y + bubble.radius < 0) clearInterval(interval);
    }, 20);
}

// ----------- SCORE & ACCURACY -----------
function updateScore() {
    score_display.textContent = score + " points";
}

function updateAccuracy() {
    if (totalBubbles === 0) {
        accuracy_display.textContent = "0%";
        return;
    }
    let accuracy = (poppedBubbles / totalBubbles) * 100;
    accuracy_display.textContent = accuracy.toFixed(1) + "%";
}

// ----------- START / STOP BUTTON -----------
let toggleBtn = document.getElementById('toggle-btn');
let gameInterval = null;

toggleBtn.addEventListener('click', () => {
    if (gameInterval !== null) {
        clearInterval(gameInterval);
        gameInterval = null;
        toggleBtn.textContent = "Start Game";
        game_area.innerHTML = "";

        // Reset stats
        /* score = 0;
        totalBubbles = 0;
        poppedBubbles = 0; */
        updateScore();
        updateAccuracy();
        return;
    }

    gameInterval = setInterval(addBubble, 500);
    toggleBtn.textContent = "Stop Game";
});
