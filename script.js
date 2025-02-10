/******************************************************
 * TYPEWRITER EFFECT (30ms per character)
 ******************************************************/
document.addEventListener('DOMContentLoaded', () => {
  const textElement = document.getElementById('typewriterText');
  const textString = textElement.textContent.trim();
  let index = 0;
  textElement.textContent = '';
  
  function typeWriter() {
    if (index < textString.length) {
      textElement.textContent += textString.charAt(index);
      index++;
      setTimeout(typeWriter, 30);
    }
  }
  typeWriter();
});

/******************************************************
 * DEVICE THEME PREFERENCE & THEME TOGGLE
 ******************************************************/
const themeToggleBtn = document.getElementById('themeToggleBtn');
if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
  document.body.classList.add('dark-mode');
  themeToggleBtn.innerHTML = '<i class="fa fa-sun"></i>';
} else {
  themeToggleBtn.innerHTML = '<i class="fa fa-moon"></i>';
}

themeToggleBtn.addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
  if (document.body.classList.contains('dark-mode')) {
    themeToggleBtn.innerHTML = '<i class="fa fa-sun"></i>';
  } else {
    themeToggleBtn.innerHTML = '<i class="fa fa-moon"></i>';
  }
});

/******************************************************
 * FADE-IN ANIMATION FOR CARDS (INTERSECTION OBSERVER)
 ******************************************************/
const cardsToAnimate = document.querySelectorAll('.card-to-animate');
const observerOptions = { threshold: 0.2 };
const observer = new IntersectionObserver((entries, obs) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('in-view');
      obs.unobserve(entry.target);
    }
  });
}, observerOptions);
cardsToAnimate.forEach(card => observer.observe(card));

/******************************************************
 * SCROLL BUTTONS: UP & DOWN NAVIGATION WITH DOUBLE-CLICK SKIP
 * (Double-click threshold set to 250ms)
 ******************************************************/
const sections = Array.from(document.querySelectorAll('section.content-section'));
const upButton = document.getElementById('upButton');
const downButton = document.getElementById('downButton');

let upClickCount = 0;
let downClickCount = 0;
const quickClickThreshold = 250;
let upTimeout;
let downTimeout;

function getCurrentSectionIndex() {
  let scrollPos = window.scrollY;
  let index = -1;
  sections.forEach((section, i) => {
    if (scrollPos >= section.offsetTop - 50) {
      index = i;
    }
  });
  return index;
}

function scrollToSection(index) {
  if (index >= 0 && index < sections.length) {
    sections[index].scrollIntoView({ behavior: 'smooth' });
  }
}

downButton.addEventListener('click', () => {
  downClickCount++;
  clearTimeout(downTimeout);
  downTimeout = setTimeout(() => { downClickCount = 0; }, quickClickThreshold);

  if (downClickCount >= 2) {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    downClickCount = 0;
    return;
  }
  
  let currentIndex = getCurrentSectionIndex();
  let nextIndex = currentIndex + 1;
  if (nextIndex >= sections.length) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } else {
    scrollToSection(nextIndex);
  }
});

upButton.addEventListener('click', () => {
  upClickCount++;
  clearTimeout(upTimeout);
  upTimeout = setTimeout(() => { upClickCount = 0; }, quickClickThreshold);

  if (upClickCount >= 2) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    upClickCount = 0;
    return;
  }
  
  let currentIndex = getCurrentSectionIndex();
  if (currentIndex <= 0) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } else {
    scrollToSection(currentIndex - 1);
  }
});

/******************************************************
 * CONTACT FORM VALIDATION & SEND MESSAGE
 ******************************************************/
const msgName = document.getElementById('msgName');
const msgEmail = document.getElementById('msgEmail');
const msgTel = document.getElementById('msgTel');
const msgText = document.getElementById('msgText');
const sendButton = document.getElementById('sendButton');

function validateForm() {
  const anyFilled = msgName.value.trim() !== "" || msgEmail.value.trim() !== "" || msgTel.value.trim() !== "";
  const messageValid = msgText.value.trim().length >= 10;
  
  sendButton.disabled = !(anyFilled && messageValid);
  
  if (!sendButton.disabled) {
    sendButton.style.backgroundColor = "var(--secondary-color)";
  } else {
    sendButton.style.backgroundColor = "#cccccc";
  }
}

[msgName, msgEmail, msgTel, msgText].forEach(input => {
  input.addEventListener('input', validateForm);
});

sendButton.addEventListener('click', (e) => {
  e.preventDefault();
  const formData = {
    name: msgName.value.trim(),
    email: msgEmail.value.trim(),
    tel: msgTel.value.trim(),
    message: msgText.value.trim()
  };

  // Use absolute path to avoid duplicate folder segments:
  fetch('/messages/save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  })
  .then(response => {
    if (response.ok) {
      alert("Message sent successfully!");
      msgName.value = "";
      msgEmail.value = "";
      msgTel.value = "";
      msgText.value = "";
      validateForm();
    } else {
      alert("There was an error sending your message.");
    }
  })
  .catch(error => {
    console.error("Error:", error);
    alert("There was an error sending your message.");
  });
});
