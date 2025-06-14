// Debounce function untuk mencegah terlalu banyak request
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Fungsi pencarian dengan loading state
function liveSearch(query) {
  const artworksDiv = document.getElementById("artworks");
  const searchBox = document.querySelector(".search-box input");

  // Tampilkan loading state
  if (query.length > 0) {
    searchBox.style.backgroundImage =
      "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='%23007bff' d='M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 12 12' to='360 12 12' dur='1s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E\")";
    searchBox.style.backgroundRepeat = "no-repeat";
    searchBox.style.backgroundPosition = "right 1rem center";
  } else {
    searchBox.style.backgroundImage = "none";
  }

  // Jika query terlalu pendek
  if (query.length < 3 && query.length > 0) {
    artworksDiv.innerHTML = `
            <div class="no-results">
                <i class="fas fa-keyboard"></i>
                <p>Masukkan minimal 3 karakter untuk mencari...</p>
            </div>`;
    return;
  }

  // Kirim request pencarian
  let xhr = new XMLHttpRequest();
  xhr.open("GET", `index.php?search=${encodeURIComponent(query)}`, true);

  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      searchBox.style.backgroundImage = "none";
      if (xhr.status === 200) {
        const match = xhr.responseText.match(
          /<div id="artworks"[^>]*>([\s\S]*?)<\/div>/
        );
        if (match) {
          artworksDiv.innerHTML = match[1];
          initializeArtworkAnimations();
        } else {
          artworksDiv.innerHTML = `
                        <div class="no-results">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Terjadi kesalahan saat memuat data</p>
                        </div>`;
        }
      } else {
        artworksDiv.innerHTML = `
                    <div class="no-results">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Terjadi kesalahan saat mencari</p>
                    </div>`;
      }
    }
  };

  xhr.onerror = function () {
    searchBox.style.backgroundImage = "none";
    artworksDiv.innerHTML = `
            <div class="no-results">
                <i class="fas fa-wifi"></i>
                <p>Terjadi kesalahan koneksi</p>
            </div>`;
  };

  xhr.send();
}

// Initialize artwork animations
function initializeArtworkAnimations() {
  const artworks = document.querySelectorAll(".artwork");
  artworks.forEach((artwork, index) => {
    artwork.style.animationDelay = `${index * 0.1}s`;
  });
}

// Attach debounced search
const debouncedSearch = debounce(liveSearch, 300);
document.getElementById("liveSearch").addEventListener("input", (e) => {
  debouncedSearch(e.target.value);
});

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
  initializeArtworkAnimations();

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      document.querySelector(this.getAttribute("href")).scrollIntoView({
        behavior: "smooth",
      });
    });
  });
});
