
document.addEventListener('DOMContentLoaded', function(){
  // simple fade-in for cards sequentially
  const cards = document.querySelectorAll('.card');
  cards.forEach((c,i)=>{
    c.style.animationDelay = (i*120)+'ms';
  });

  // Mobile nav toggle (if needed)
  const toggle = document.querySelector('.nav-toggle');
  if(toggle){
    toggle.addEventListener('click', ()=>{
      document.querySelector('.nav').classList.toggle('open');
    });
  }
});
