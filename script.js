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
 ***************************************  ***************/
const msgName = document.getElementById('msgName');
const email1 = document.getElementById("msgEmail1");
const email2 = document.getElementById("msgEmail2");
const countryInput = document.getElementById("msgCountryCode");
const telInput = document.getElementById("msgTel");
const msgText = document.getElementById('msgText');
const sendButton = document.getElementById('sendButton');

// Enable send button if at least one contact field is filled and message is long enough.
function checkFields() {
  const nameFilled = msgName.value.trim() !== "";
  const emailFilled = (email1.value.trim() !== "" && email2.value.trim() !== "");
  const telFilled = (countryInput.value.trim() !== "" && telInput.value.trim() !== "");
  const messageValid = msgText.value.trim().length >= 7;
  
  sendButton.disabled = !( (nameFilled || emailFilled || telFilled) && messageValid );
}

[msgName, email1, email2, countryInput, telInput, msgText].forEach(field => {
  field.addEventListener("input", checkFields);
});

sendButton.addEventListener("click", function(e) {
  e.preventDefault();
  
  // Check validations
  if (msgText.value.trim().length < 7) {
    showToast("Error: Message must be at least 7 characters long.", "error");
    return;
  }
  if (telInput.value.trim().length !== 10) {
    showToast("Error: Telephone number must be exactly 10 digits.", "error");
    return;
  }
  
  // Auto-fill defaults if any contact field is empty
  if (msgName.value.trim() === "") {
    msgName.value = "John Doe";
  }
  if (email1.value.trim() === "" || email2.value.trim() === "") {
    email1.value = "john";
    email2.value = "doe.com";
  }
  if (countryInput.value.trim() === "") {
    countryInput.value = "+90";
  }
  
  // Prepare the data object to send
  const formData = {
    name: msgName.value.trim(),
    email: email1.value.trim() + "@" + email2.value.trim(),
    tel: countryInput.value.trim() + telInput.value.trim(),
    message: msgText.value.trim()
  };

  // Send data to your server via POST
  fetch('/messages/save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  })
  .then(response => {
    if (response.ok) {
      showToast("Message sent!", "success");
      // Clear the form fields
      msgName.value = "";
      email1.value = "";
      email2.value = "";
      countryInput.value = "+90";
      telInput.value = "";
      msgText.value = "";
      sendButton.disabled = true;
    } else {
      showToast("Error: Could not send message.", "error");
    }
  })
  .catch(error => {
    console.error("Error:", error);
    showToast("Error: Could not send message.", "error");
  });
});
