
// Typing Animation

const text = "Welcome to My Portfolio";
let index = 0;

function typing(){

if(index < text.length){

document.getElementById("typing").innerHTML += text.charAt(index);

index++;

setTimeout(typing,100);

}

}

typing();



// PROJECT DATA (Dynamic Content)

const projects = [

{
title:"Machine Learning Disease Prediction",
desc:"Predict diseases using ML algorithms.",
img:"images/project1.jpg",
link:"#"
},

{
title:"Dairy Farm Management System",
desc:"C# Windows Forms software for farm management.",
img:"images/project2.jpg",
link:"#"
},

{
title:"Portfolio Website",
desc:"Personal responsive website using HTML CSS JS.",
img:"images/project3.jpg",
link:"#"
}

];

const container = document.getElementById("projectContainer");

projects.forEach(p => {

container.innerHTML += `

<div class="card">

<img src="${p.img}">

<div class="card-body">

<h3>${p.title}</h3>

<p>${p.desc}</p>

<a href="${p.link}">View Project</a>

</div>

</div>

`;

});



// FORM VALIDATION

document.getElementById("contactForm").addEventListener("submit", function(e){

e.preventDefault();

let valid = true;

const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

let name = document.getElementById("name").value;
let email = document.getElementById("email").value;
let subject = document.getElementById("subject").value;
let message = document.getElementById("message").value;

if(name===""){
document.getElementById("nameError").innerText="Name required";
valid=false;
}else{
document.getElementById("nameError").innerText="";
}

if(!email.match(emailPattern)){
document.getElementById("emailError").innerText="Valid email required";
valid=false;
}else{
document.getElementById("emailError").innerText="";
}

if(subject===""){
document.getElementById("subjectError").innerText="Subject required";
valid=false;
}else{
document.getElementById("subjectError").innerText="";
}

if(message===""){
document.getElementById("messageError").innerText="Message required";
valid=false;
}else{
document.getElementById("messageError").innerText="";
}

if(valid){
alert("Message sent successfully!");
}

});



// DARK MODE

const toggle = document.getElementById("themeToggle");

if(localStorage.getItem("theme") === "dark"){
document.body.classList.add("dark");
}

toggle.onclick = function(){

document.body.classList.toggle("dark");

if(document.body.classList.contains("dark")){

localStorage.setItem("theme","dark");

}else{

localStorage.setItem("theme","light");

}

};



// SCROLL TO TOP

const topBtn = document.getElementById("topBtn");

window.onscroll = function(){

if(document.documentElement.scrollTop > 200){

topBtn.style.display = "block";

}else{

topBtn.style.display = "none";

}

};

topBtn.onclick = function(){

window.scrollTo({
top:0,
behavior:"smooth"
});

};