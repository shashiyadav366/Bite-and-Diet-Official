<canvas id="fireworks"></canvas>

<style>
  #fireworks {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none; /* don’t block clicks */
    z-index: 9999; /* on top of everything */
  }
</style>

<script>
  const API_URL = "test/festival_dates.json"; // your JSON file
  const today = new Date();
  const todayStr = today.toLocaleDateString("en-US", {
    month: "2-digit",
    day: "2-digit",
    year: "numeric"
  });

  const festivalColors = {
    "New Year's Day": ["#ff4e50", "#f9d423", "#24c6dc", "#514a9d"],
    "Makar Sankranti": ["#ff9933", "#ffd700", "#1e90ff", "#32cd32"],
    "Republic Day": ["#ff9933", "#ffffff", "#138808", "#000080"],
    "Valentine's Day": ["#ff4b2b", "#ff416c", "#ff6f91", "#ff99ac"],
    "Maha Shivaratri/Shivaratri": ["#2c3e50", "#8e44ad", "#34495e", "#1abc9c"],
    "Holi": ["#ff6a00", "#ee0979", "#ffcc70", "#8e54e9"],
    "Rama Navami": ["#f1c40f", "#e67e22", "#d35400", "#c0392b"],
    "Mothers' Day": ["#ff9a9e", "#fad0c4", "#fbc2eb", "#a1c4fd"],
    "Guru Purnima": ["#9b59b6", "#8e44ad", "#2980b9", "#f39c12"],
    "Friendship Day": ["#00c6ff", "#0072ff", "#f7971e", "#ffd200"],
    "Raksha Bandhan (Rakhi)": ["#ff512f", "#dd2476", "#ff9966", "#ff5e62"],
    "Independence Day": ["#ff9933", "#ffffff", "#138808", "#000080"],
    "Janmashtami": ["#f9d423", "#24c6dc", "#a1c4fd", "#ffdde1"],
    "Ganesh Chaturthi/Vinayaka Chaturthi": ["#e67e22", "#d35400", "#f39c12", "#27ae60"],
    "First Day of Sharad Navratri": ["#d35400", "#c0392b", "#8e44ad", "#f1c40f"],
    "First Day of Durga Puja Festivities": ["#e74c3c", "#c0392b", "#f39c12", "#2c3e50"],
    "Maha Navami": ["#c0392b", "#e74c3c", "#f1c40f", "#9b59b6"],
    "Mahatma Gandhi Jayanti": ["#ff9933", "#ffffff", "#138808", "#bdc3c7"],
    "Dussehra": ["#f39c12", "#d35400", "#c0392b", "#27ae60"],
    "Karaka Chaturthi (Karva Chauth)": ["#e84393", "#fd79a8", "#fab1a0", "#ffeaa7"],
    "Diwali/Deepavali": ["#ff9a9e", "#fad0c4", "#ffdde1", "#fbc2eb"],
    "Chhat Puja (Pratihar Sashthi/Surya Sashthi)": ["#f1c40f", "#e67e22", "#d35400", "#2980b9"],
    "Christmas": ["#2ecc71", "#27ae60", "#e74c3c", "#c0392b"],
    "New Year's Eve": ["#00c6ff", "#0072ff", "#f7971e", "#ffd200"]
  };

  function hexToRgb(hex) {
    hex = hex.replace("#", "");
    if (hex.length === 3) {
      hex = hex.split("").map(c => c + c).join("");
    }
    const bigint = parseInt(hex, 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `${r},${g},${b}`;
  }

  async function loadFestivals() {
    try {
      const res = await fetch(API_URL);
      const festivals = await res.json();
      const found = festivals.find(f => f.date === todayStr);

      if (found) {
        const colors = (festivalColors[found.name] || ["#ffffff"]).map(hexToRgb);
        startFireworks(colors);
      }
    } catch (e) {
      console.error("Failed to load festivals.json", e);
    }
  }

  function startFireworks(colors) {
    const canvas = document.getElementById("fireworks");
    const ctx = canvas.getContext("2d");
    let w, h;
    let particles = [];

    function setSize() {
      w = canvas.width = window.innerWidth;
      h = canvas.height = window.innerHeight;
    }
    window.addEventListener("resize", setSize);
    setSize();

    function random(min, max) {
      return Math.random() * (max - min) + min;
    }

    class Particle {
      constructor(x, y, color) {
        this.x = x;
        this.y = y;
        this.color = color;
        this.radius = random(2, 5);
        this.speedX = random(-3, 3);
        this.speedY = random(-3, 3);
        this.alpha = 1;
        this.decay = random(0.01, 0.02);
      }
      update() {
        this.x += this.speedX;
        this.y += this.speedY;
        this.alpha -= this.decay;
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${this.color},${this.alpha})`;
        ctx.fill();
      }
    }

    function createFirework() {
      const x = random(100, w - 100);
      const y = random(100, h / 2);
      const color = colors[Math.floor(Math.random() * colors.length)];
      for (let i = 0; i < 50; i++) {
        particles.push(new Particle(x, y, color));
      }
    }

    function animate() {
      ctx.clearRect(0, 0, w, h);
      particles.forEach((p, i) => {
        p.update();
        p.draw();
        if (p.alpha <= 0) particles.splice(i, 1);
      });
      requestAnimationFrame(animate);
    }

    setInterval(createFirework, 1200);
    animate();
  }

  loadFestivals();
</script>

