{{-- Searchable dropdowns preserve the original native select for form submission. --}}
<style>
.gym-picker{position:relative;min-width:0;flex:0 1 auto;width:100%}
.gym-picker-native{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important;inset-inline-start:0;bottom:0}
.gym-picker-trigger{width:100%;min-height:43px;display:flex;align-items:center;justify-content:space-between;gap:12px;direction:rtl;text-align:right;border:1px solid #39485d;background:#111827;color:#e5edf8;border-radius:12px;padding:9px 12px;cursor:pointer;font-size:13px}
.gym-picker-trigger:focus-visible{outline:2px solid #60a5fa;outline-offset:2px}
.gym-picker-label{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0}
.gym-picker-arrow{flex-shrink:0;color:#93a4bd;font-size:14px}
.gym-picker-popover{position:fixed;z-index:10025;background:#1b2636;color:#eaf1fa;border:1px solid #3b4b62;border-radius:14px;box-shadow:0 18px 50px rgba(0,0,0,.28);padding:10px;box-sizing:border-box;direction:rtl;max-width:calc(100vw - 20px)}
.gym-picker-popover[hidden],.gym-picker-backdrop[hidden]{display:none!important}
.gym-picker-search{display:block;box-sizing:border-box;width:100%;min-height:43px;background:#101a29!important;color:#eaf1fa!important;border:1px solid #40536d!important;border-radius:10px;padding:9px 12px;outline:none;font-size:16px}
.gym-picker-search:focus{border-color:#60a5fa!important;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.gym-picker-results{margin-top:7px;max-height:min(235px,38vh);overflow-y:auto;overscroll-behavior:contain;scrollbar-width:none!important;-ms-overflow-style:none!important}
.gym-picker-results::-webkit-scrollbar{display:none!important;width:0!important;height:0!important}
.gym-picker-results::-webkit-scrollbar-thumb{background:#64748b;border-radius:99px}
.gym-picker-item{width:100%;display:block;text-align:right;direction:rtl;border:0;background:transparent;color:inherit;border-radius:9px;padding:10px 12px;font-size:12px;line-height:1.75;cursor:pointer;overflow-wrap:anywhere}
.gym-picker-item:hover,.gym-picker-item:focus-visible,.gym-picker-item[aria-selected="true"]{background:#284a78;color:#fff;outline:none}
.gym-picker-group{font-size:11px;font-weight:bold;color:#9fc4ff;padding:10px 12px 4px}
.gym-picker-empty{padding:18px 10px;text-align:center;color:#b4c1d3;font-size:12px}
.gym-picker-backdrop{display:none}
html[data-theme="light"] .gym-picker-trigger{background:#fff;color:#17324d;border-color:#c8d6e6}
html[data-theme="light"] .gym-picker-popover{background:#fff;color:#17324d;border-color:#d7e2ed;box-shadow:0 20px 55px rgba(21,39,67,.16)}
html[data-theme="light"] .gym-picker-search{background:#f7fafe!important;color:#17324d!important;border-color:#c8d6e6!important}
html[data-theme="light"] .gym-picker-item:hover,html[data-theme="light"] .gym-picker-item:focus-visible,html[data-theme="light"] .gym-picker-item[aria-selected="true"]{background:#e9f1ff;color:#1d4ed8}
html[data-theme="light"] .gym-picker-group{color:#1d4ed8}
@media(max-width:640px){
 .gym-picker{flex:none!important;width:100%!important;min-height:0!important}
 .gym-picker-backdrop:not([hidden]){display:block;position:fixed;inset:0;background:rgba(2,6,23,.58);z-index:10020}
 .gym-picker-popover{top:auto!important;bottom:var(--gym-keyboard-inset, 0px)!important;left:0!important;right:0!important;width:100%!important;max-width:100%!important;border-radius:22px 22px 0 0;padding:15px;padding-bottom:max(15px,env(safe-area-inset-bottom));max-height:76dvh}
 /* Input won't trigger iOS auto-zoom. Limit list height when keyboard opens. */
 .gym-picker-search{font-size:16px!important;min-height:46px}
 .gym-picker-popover{max-height:min(76dvh,calc(var(--gym-visual-viewport-height,100dvh) - 12px))}
 .gym-picker-results{max-height:min(calc(76dvh - 105px),calc(var(--gym-visual-viewport-height,100dvh) - 105px));scrollbar-width:none}
 .gym-picker-results::-webkit-scrollbar{display:none!important;width:0!important;height:0!important}
}
/* Program cards: allow scrolling without oversized browser scroll tracks. */
html .gym-picker-results, html .gym-picker-popover {scrollbar-width:none!important;-ms-overflow-style:none!important}
html .gym-picker-results::-webkit-scrollbar, html .gym-picker-popover::-webkit-scrollbar{display:none!important;width:0!important;height:0!important}
html [class*="overflow-y-auto"]:has(> li),html [class*="overflow-y-scroll"]:has(> li){scrollbar-width:none}
html [class*="overflow-y-auto"]:has(> li)::-webkit-scrollbar,html [class*="overflow-y-scroll"]:has(> li)::-webkit-scrollbar{display:none}
</style>
<script>
(() => {
 'use strict';
 const normalize = str => String(str || '').toLocaleLowerCase('ar').normalize('NFKC').replace(/[\u064b-\u065f\u0670]/g, '').replace(/[أإآ]/g, 'ا').trim();
 let current = null;
 function enhance(select) {
   if (select.dataset.gymPicker || select.multiple || select.disabled || select.closest('.gym-picker')) return;
   const choices = [...select.options];
   const remoteUrl = select.dataset.foodSearchUrl || '';
   let requestId = 0;
   let debounceHandle = null;
   if (!select.matches('[data-searchable-select], select[name="food_id"]') && choices.length < 16) return;
   select.dataset.gymPicker = 'true';
   const container = document.createElement('div'); container.className = 'gym-picker';
   const trigger = document.createElement('button'); trigger.type='button'; trigger.className='gym-picker-trigger'; trigger.setAttribute('aria-haspopup','listbox');trigger.setAttribute('aria-expanded','false');
   const label = document.createElement('span'); label.className='gym-picker-label';
   const arrow = document.createElement('span'); arrow.className='gym-picker-arrow'; arrow.textContent='⌄';
   trigger.append(label, arrow);
   const backdrop=document.createElement('div');backdrop.className='gym-picker-backdrop';backdrop.hidden=true;
   const popover=document.createElement('div');popover.className='gym-picker-popover';popover.hidden=true;
   const search=document.createElement('input');search.type='search';search.className='gym-picker-search';search.placeholder='ابحث عن صنف أو تمرين...';search.setAttribute('aria-label','البحث داخل قائمة الاختيار');search.autocomplete='off';
   const results=document.createElement('div');results.className='gym-picker-results';results.setAttribute('role','listbox');
   popover.append(search,results);
   select.parentNode.insertBefore(container,select);container.append(select,trigger);select.classList.add('gym-picker-native');
   document.body.append(backdrop,popover);
   function sync(){const selected=select.selectedOptions[0];label.textContent=selected?.textContent.trim() || 'اختر من القائمة...';}
   function locate(){if(popover.hidden || matchMedia('(max-width: 640px)').matches)return;const rect=trigger.getBoundingClientRect();const width=Math.min(Math.max(rect.width,350),window.innerWidth-24);const x=Math.min(Math.max(12,rect.right-width),window.innerWidth-width-12);popover.style.left=x+'px';popover.style.right='auto';popover.style.width=width+'px';const spaceBelow=window.innerHeight-rect.bottom;const height=Math.min(290,window.innerHeight*.48);if(spaceBelow>=height+8||spaceBelow>=rect.top){popover.style.top=(rect.bottom+6)+'px';popover.style.bottom='auto'}else{popover.style.top='auto';popover.style.bottom=(window.innerHeight-rect.top+6)+'px'}}
   function close(){
     requestId++;
     clearTimeout(debounceHandle);
     popover.hidden=true;backdrop.hidden=true;
     trigger.setAttribute('aria-expanded','false');
     if(current?.close===close)current=null;
   }
   function message(value){
     const notice=document.createElement('p');
     notice.className='gym-picker-empty';
     notice.setAttribute('role','status');
     notice.textContent=value;
     results.replaceChildren(notice);
   }
   function render(text, source=choices, serverFiltered=false){
     results.replaceChildren();
     const query=normalize(text);
     let count=0;let lastGroup='';
     const fragment=document.createDocumentFragment();
     for(const option of source){
       if(!option.value || option.disabled)continue;
       const group=option.parentElement instanceof HTMLOptGroupElement ? option.parentElement.label : '';
       if(!serverFiltered && query && !normalize(option.textContent+' '+group).includes(query))continue;
       if(count>=100)break;
       if(group!==lastGroup && group){
         const heading=document.createElement('div');heading.className='gym-picker-group';heading.textContent=group;fragment.append(heading);
       }
       lastGroup=group;
       const item=document.createElement('button');
       item.type='button';item.className='gym-picker-item';
       item.textContent=option.textContent.trim();
       item.setAttribute('role','option');
       item.setAttribute('aria-selected',String(select.value===option.value));
       item.addEventListener('click',()=>{
         // Keep the real select: submitting a meal never creates a Food record.
         if(remoteUrl && ![...select.options].some(entry=>entry.value===option.value)){
           select.add(new Option(option.textContent.trim(),option.value));
         }
         select.value=option.value;
         select.dispatchEvent(new Event('input',{bubbles:true}));
         select.dispatchEvent(new Event('change',{bubbles:true}));
         sync();close();trigger.focus();
       });
       fragment.append(item);count++;
     }
     if(!count){
       const empty=document.createElement('p');empty.className='gym-picker-empty';empty.textContent='لا توجد نتائج مطابقة';fragment.append(empty);
     }
     results.append(fragment);
   }
   function searchRemote(term){
     clearTimeout(debounceHandle);
     const id=++requestId;
     if(!term.trim()){render('',choices);return;}
     message('جاري البحث في بنك الطعام...');
     debounceHandle=setTimeout(async()=>{
       try{
         const url=new URL(remoteUrl,window.location.href);
         url.searchParams.set('q',term.trim());
         const response=await fetch(url.toString(),{
           headers:{'Accept':'application/json'},credentials:'same-origin',cache:'no-store'
         });
         if(!response.ok)throw new Error('Failed to fetch food options');
         const payload=await response.json();
         if(id!==requestId || popover.hidden)return;
         const loaded=Array.isArray(payload.foods) ? payload.foods.map(food=>new Option(food.label,String(food.id))) : [];
         render(term,loaded,true);
       }catch(error){
         if(id===requestId && !popover.hidden)message('تعذر البحث الآن. حاول مرة أخرى.');
       }
     },180);
   }
   function open(){
     if(current)current.close();
     current={close};search.value='';
     search.placeholder=remoteUrl?'ابحث عن صنف الطعام...':'ابحث عن صنف أو تمرين...';
     render('');popover.hidden=false;backdrop.hidden=false;
     trigger.setAttribute('aria-expanded','true');
     locate();requestAnimationFrame(()=>search.focus());
   }
   trigger.addEventListener('click',()=>popover.hidden?open():close());
   backdrop.addEventListener('click',close);
   search.addEventListener('input',()=>remoteUrl?searchRemote(search.value):render(search.value));
   search.addEventListener('keydown',event=>{
     if(event.key==='Escape'){close();trigger.focus()}
     else if(event.key==='Enter'){event.preventDefault();results.querySelector('.gym-picker-item')?.click()}
   });
   window.addEventListener('scroll',locate,{passive:true,capture:true});window.addEventListener('resize',locate);
   document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!popover.hidden){close();trigger.focus()}});
   select.addEventListener('change',sync);select.addEventListener('invalid',()=>{close();trigger.focus()});sync();
 }
 const scan=root=>root.querySelectorAll?.('select').forEach(enhance);
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>scan(document));else scan(document);
 const observer=new MutationObserver(muts=>{for(const m of muts)for(const node of m.addedNodes){if(node.nodeType!==1)continue;if(node.matches?.('select'))enhance(node);else if(!node.classList?.contains('gym-picker-popover'))scan(node)}});
 observer.observe(document.documentElement,{childList:true,subtree:true});
})();
</script>

<style>
/* Prevent the picker from stretching the mobile meal form vertically. */
.gym-picker { min-height:0; align-self:stretch; }
.gym-picker-popover, .gym-picker-results { scrollbar-width:none !important; -ms-overflow-style:none !important; }
.gym-picker-popover::-webkit-scrollbar, .gym-picker-results::-webkit-scrollbar { display:none !important; width:0 !important; height:0 !important; }
@media(min-width:641px) { .gym-picker { flex:1 1 0%; width:auto; } }
@media(max-width:640px) { .gym-picker { flex:0 0 auto !important; width:100% !important; } }
</style>
