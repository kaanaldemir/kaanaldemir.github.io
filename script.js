// Typewriter effect for hero section
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

// Theme toggle functionality
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

// Animate cards on scroll
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

// Scroll navigation buttons functionality
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

// Form field validations and submission
const msgName = document.getElementById('msgName');
const email1 = document.getElementById("msgEmail1");
const email2 = document.getElementById("msgEmail2");
const countryInput = document.getElementById("msgCountryCode");
const telInput = document.getElementById("msgTel");
const msgText = document.getElementById('msgText');
const sendButton = document.getElementById('sendButton');
function checkFields() {
  const nameFilled = msgName.value.trim() !== "";
  const emailFilled = (email1.value.trim() !== "" && email2.value.trim() !== "");
  const telFilled = (countryInput.value.trim() !== "" && telInput.value.trim() !== "");
  const messageValid = msgText.value.trim().length >= 7;
  sendButton.disabled = !((nameFilled || emailFilled || telFilled) && messageValid);
}
[msgName, email1, email2, countryInput, telInput, msgText].forEach(field => {
  field.addEventListener("input", checkFields);
});
sendButton.addEventListener("click", function(e) {
  e.preventDefault();
  if (msgText.value.trim().length < 7) {
    showToast("Error: Message must be at least 7 characters long.", "error");
    return;
  }
  if (telInput.value.trim().length !== 10) {
    showToast("Error: Telephone number must be exactly 10 digits.", "error");
    return;
  }
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
  const formData = {
    name: msgName.value.trim(),
    email: email1.value.trim() + "@" + email2.value.trim(),
    tel: countryInput.value.trim() + telInput.value.trim(),
    message: msgText.value.trim()
  };
  fetch("/relay.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast("Message sent!", "success");
      msgName.value = "";
      email1.value = "";
      email2.value = "";
      countryInput.value = "+90";
      telInput.value = "";
      msgText.value = "";
      sendButton.disabled = true;
    } else {
      showToast("" + (data.error || "Unknown error"), "error");
    }
  })
  .catch(error => {
    console.error("Error:", error);
    showToast("Could not send message.", "error");
  });
});

// Hamburger menu toggle
document.getElementById('hamburger-menu').addEventListener('click', function() {
  document.body.classList.toggle('menu-open');
  this.classList.toggle('active');
});

// Email and telephone input formatting
document.addEventListener("DOMContentLoaded", function(){
  var email1 = document.getElementById("msgEmail1"),
      email2 = document.getElementById("msgEmail2");
  email1.addEventListener("input", function(){
    if(this.value.indexOf("@") !== -1){
      this.value = this.value.replace("@", "");
      email2.focus();
    }
  });
  var telInput = document.getElementById("msgTel");
  telInput.addEventListener("input", function(){
    this.value = this.value.replace(/\D/g, "");
  });
  var countryInput = document.getElementById("msgCountryCode");
  countryInput.addEventListener("input", function(){
    this.value = this.value.replace(/[^0-9+]/g, "");
  });
  var sendButton = document.getElementById("sendButton"),
      msgName = document.getElementById("msgName"),
      msgText = document.getElementById("msgText");
  function checkFields(){
    var anyContact = msgName.value.trim() !== "" || email1.value.trim() !== "" || email2.value.trim() !== "" || telInput.value.trim() !== "";
    var messageValid = msgText.value.trim().length >= 10;
    sendButton.disabled = !(anyContact && messageValid);
  }
  [msgName, email1, email2, countryInput, telInput, msgText].forEach(function(field){
    field.addEventListener("input", checkFields);
  });
  function showToast(message, type){
    var toast = document.createElement("div");
    toast.classList.add("toast");
    toast.classList.add(type === "success" ? "toast-success" : "toast-error");
    toast.textContent = message;
    var container = document.getElementById("toast-container");
    if(!container){
      container = document.createElement("div");
      container.id = "toast-container";
      document.body.appendChild(container);
    }
    container.appendChild(toast);
    setTimeout(function(){
      toast.remove();
    }, 3500);
  }
  sendButton.addEventListener("click", function(e){
    e.preventDefault();
    var messageLen = msgText.value.trim().length;
    if(messageLen < 10){
      showToast("Message must be at least 10 characters long.", "error");
      return;
    }
    if(email1.value.trim() === "" && email2.value.trim() === ""){
      email1.value = "john";
      email2.value = "doe.com";
    } else if(email1.value.trim() === "" || email2.value.trim() === ""){
      showToast("Both email fields must be filled in.", "error");
      return;
    }
    if(telInput.value.trim() === ""){
      telInput.value = "5555555555";
    }
    if(telInput.value.trim().length !== 10){
      showToast("Telephone number must be 10 digits.", "error");
      return;
    }
    if(msgName.value.trim() === ""){
      msgName.value = "John Doe";
    }
    if(countryInput.value.trim() === ""){
      countryInput.value = "+90";
    }
    var formData = {
      name: msgName.value.trim(),
      email: email1.value.trim() + "@" + email2.value.trim(),
      tel: countryInput.value.trim() + telInput.value.trim(),
      message: msgText.value.trim()
    };
    fetch("/relay.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      if(data.success){
        showToast("Message sent!", "success");
        msgName.value = "";
        email1.value = "";
        email2.value = "";
        countryInput.value = "+90";
        telInput.value = "";
        msgText.value = "";
        sendButton.disabled = true;
      } else {
        showToast("" + (data.error || "Unknown error"), "error");
      }
    })
    .catch(error => {
      console.error("Error:", error);
      showToast("Could not send message.", "error");
    });
  });
});

// Smooth scroll for anchor links when page loads with a hash
window.addEventListener('load', () => {
  if(window.location.hash){
    const targetId = window.location.hash;
    const targetElement = document.querySelector(targetId);
    if(targetElement){
      window.scrollTo(0, 0);
      setTimeout(() => {
        targetElement.scrollIntoView({ behavior: 'smooth' });
      }, 100);
    }
  }
});

window.addEventListener('resize', () => {
  if (window.innerWidth > 1080 && document.body.classList.contains('menu-open')) {
    document.body.classList.remove('menu-open');
    document.getElementById('hamburger-menu').classList.remove('active');
  }
});
email1.addEventListener("input", function () {
  if (this.value.includes("@")) {
    const parts = this.value.split("@");
    // Use the first part for email1 and move the remainder to email2
    this.value = parts[0];
    // Only fill email2 if it’s empty so that it doesn’t override any manual edits
    if (!email2.value) {
      email2.value = parts.slice(1).join("@");
    }
    email2.focus();
  }
});