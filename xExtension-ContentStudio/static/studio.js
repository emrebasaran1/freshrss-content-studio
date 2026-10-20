/* SPDX-License-Identifier: GPL-3.0-or-later */
(()=>{'use strict';
 const core=window.ContentStudioCore,root=document.documentElement;
 let cfg,m,t,theme='minimal',tone='night',kind='',scheduled=false,active=null,player=null,pane=null;
 const tracked=new WeakSet(),rows=[],cache=new Map();
 function el(tag,cls,text){const n=document.createElement(tag);if(cls)n.className=cls;if(text!==undefined)n.textContent=text;return n;}
 function action(text,label,run){const b=el('button','studio-action',text);b.type='button';b.title=label;b.setAttribute('aria-label',label);b.onclick=e=>{e.preventDefault();e.stopPropagation();run(b);};return b;}
 function external(text,label,url){const a=el('a','studio-action',text);a.href=url;a.target='_blank';a.rel='noopener noreferrer';a.title=label;a.setAttribute('aria-label',label);return a;}
 function safeLink(value){try{const u=new URL(value,location.href);return ['https:','http:'].includes(u.protocol)?u.href:null;}catch{return null;}}
 function apply(){
  root.dataset.rssAppearance=theme;root.dataset.rssVariant=theme;root.dataset.rssTone=tone;root.dataset.rssMode=tone==='day'?'day':'night';root.dataset.rssStyled='1';
  try{localStorage.setItem('content-studio-theme',theme);localStorage.setItem('content-studio-tone',tone);}catch{}
  document.querySelectorAll('[data-studio-theme]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.studioTheme===theme)));
  document.querySelectorAll('[data-studio-tone]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.studioTone===tone)));
 }
 function appearance(){
  const bar=el('section','rss-appearance');bar.setAttribute('aria-label',t('appearance'));bar.append(el('span','rss-appearance-caption',t('appearance')));
  const choices=el('div','rss-appearance-choices');choices.setAttribute('role','group');choices.setAttribute('aria-label',t('themes'));
  for(const [id,label] of [['minimal','Minimalist'],['brutal','Brutalism'],['luxury','Luxury'],['rolex-blue','Blue Face'],['nautilus','Nautilus']]){
   const b=action(label,label,()=>{theme=id;apply();});b.dataset.studioTheme=id;choices.append(b);
  }bar.append(choices);
  const modes=el('div','rss-appearance-modes');modes.setAttribute('role','group');modes.setAttribute('aria-label',t('mode'));
  for(const id of ['day','night','dark']){const b=action(t(id),t(id),()=>{tone=id;apply();});b.dataset.studioTone=id;modes.append(b);}bar.append(modes);
  const anchor=document.querySelector('#global .nav_menu')||document.querySelector('header.header');if(!anchor)return;anchor.after(bar);
  const nav=el('nav','content-streams');nav.setAttribute('aria-label',t('streams'));
  for(const k of ['news','youtube','newsletters','blogs'])if(Number(cfg.categories[k])>0){const u=new URL(location.href);u.search='';u.searchParams.set('get','c_'+cfg.categories[k]);u.searchParams.set('order','DESC');u.searchParams.set('sort','date');u.searchParams.set('state','3');const a=el('a','',t(k));a.href=u.href;if(kind===k)a.setAttribute('aria-current','page');nav.append(a);}bar.before(nav);apply();
 }
 function modal(label,build){const d=el('dialog','studio-dialog');d.setAttribute('aria-label',label);d.append(el('h2','',label));build(d);d.append(action('×',t('close'),()=>{d.close();d.remove();}));d.addEventListener('close',()=>d.remove(),{once:true});document.body.append(d);d.showModal();}
 function cleanContent(source,cover){
  const template=el('template');template.innerHTML=source;
  template.content.querySelectorAll('script,style,form,input,button,object,embed,svg,audio,track,span[hidden]').forEach(n=>n.remove());
  const seen=new Set(),key=s=>{try{const u=new URL(s,location.href);return u.origin+u.pathname.replace(/-\d+x\d+(?=\.)/,'');}catch{return s;}};if(cover)seen.add(key(cover));
  template.content.querySelectorAll('*').forEach(n=>{
   for(const a of [...n.attributes])if(/^on/i.test(a.name)||['srcdoc','style'].includes(a.name))n.removeAttribute(a.name);
   if(/^(REKLAM|ADVERTISEMENT)$/i.test(n.textContent.trim())&&n.childElementCount===0){n.remove();return;}
   for(const attr of ['href','src','poster'])if(n.hasAttribute(attr)){const u=safeLink(n.getAttribute(attr));if(!u)n.removeAttribute(attr);else n.setAttribute(attr,u);}
   if(n.tagName==='IFRAME'){
    const src=n.getAttribute('src')||'';if(!/^https:\/\/(?:www\.)?youtube(?:-nocookie)?\.com\/embed\/[\w-]{11}(?:[?].*)?$|^https:\/\/player\.vimeo\.com\/video\/\d+(?:[?].*)?$/.test(src)){n.remove();return;}
    n.referrerPolicy='strict-origin-when-cross-origin';n.allow='encrypted-media; picture-in-picture; fullscreen';n.allowFullscreen=true;n.loading='lazy';
   }
   if(n.tagName==='A'){n.target='_blank';n.rel='noopener noreferrer';}
   if(n.tagName==='IMG'){const k=key(n.src);if(seen.has(k)){n.remove();return;}seen.add(k);n.loading='lazy';}
  });
  template.content.querySelectorAll('table').forEach(table=>{const wrap=el('div','studio-table');wrap.tabIndex=0;wrap.setAttribute('role','region');wrap.setAttribute('aria-label',t('table'));table.before(wrap);wrap.append(table);});
  return template.content;
 }
 function stopPlayer(){if(player){player.close();player.remove();player=null;}if(!pane?.open)document.body.classList.remove('studio-overlay-open');}
 function watch(row){
  stopPlayer();if(pane?.open)pane.close();const d=el('dialog','studio-player');player=d;d.setAttribute('aria-label',row.title);
  d.append(action('×',t('close'),stopPlayer),el('h2','',row.title));const f=el('iframe');f.title=row.title;
  const src=new URL('https://www.youtube.com/embed/'+row.video);src.searchParams.set('playsinline','1');src.searchParams.set('rel','0');src.searchParams.set('origin',location.origin);
  f.src=src.href;f.referrerPolicy='strict-origin-when-cross-origin';f.allow='encrypted-media; picture-in-picture; fullscreen';f.allowFullscreen=true;d.append(f);
  d.addEventListener('close',()=>{f.remove();d.remove();if(player===d)player=null;document.body.classList.remove('studio-overlay-open');},{once:true});document.body.append(d);d.showModal();document.body.classList.add('studio-overlay-open');
 }
 async function read(row,body,status,force=false){
  body.replaceChildren(cleanContent(row.html,row.cover));status.textContent='';
  if(!cfg.fullText||(!force&&kind==='news'))return;
  status.textContent=t('loading');
  try{
   let result=cache.get(row.id);if(!result){const response=await fetch('?c=contentStudio&a=reader',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({_csrf:context.csrf,id:row.id})});result=await response.json();if(!response.ok)throw Error(t(result.error||'fetch_failed'));cache.set(row.id,result);}
   if(active!==row)return;
   if(!row.cover&&result.image){row.cover=result.image;const head=row.card.querySelector('.flux_header');if(head&&!head.querySelector('.thumbnail')){const box=el('span','thumbnail'),img=el('img');img.src=result.image;img.alt='';img.loading='lazy';box.append(img);head.prepend(box);}}
   body.replaceChildren(cleanContent(result.html,row.cover||result.image));status.textContent=result.notice||'';
  }catch(error){if(active===row)status.textContent=error.message||t('fetch_failed');}
 }
 function openArticle(row){
  if(row.video){watch(row);return;}
  if(active===row&&row.panel&&!row.panel.hidden){row.panel.hidden=true;active=null;return;}
  if(active?.panel)active.panel.hidden=true;stopPlayer();active=row;
  if(['blogs','newsletters'].includes(kind)){
   if(!pane){pane=el('dialog','studio-reader');pane.setAttribute('aria-label',t('reader'));document.body.append(pane);pane.addEventListener('close',()=>{pane.replaceChildren();active=null;document.body.classList.remove('studio-overlay-open');});}
   pane.replaceChildren();pane.append(action('×',t('close'),()=>pane.close()),el('h2','',row.title));pane.append(toolbar(row));const status=el('p','studio-status'),body=el('div','studio-body');pane.append(status,body);if(!pane.open)pane.showModal();document.body.classList.add('studio-overlay-open');read(row,body,status,true);
  }else{
   if(!row.panel){row.panel=el('section','studio-inline');row.card.append(row.panel);}row.panel.hidden=false;row.panel.replaceChildren();const status=el('p','studio-status'),body=el('div','studio-body');row.panel.append(status,body);read(row,body,status,true);
  }
 }
 function download(row){
  modal(t('download'),d=>{const quality=el('select');quality.setAttribute('aria-label',t('download'));for(const q of ['720','1080','2160','best']){const o=el('option','',q==='best'?'Best':q+'p');o.value=q;quality.append(o);}d.append(quality);const status=el('p','studio-status');d.append(status);d.append(action(t('queue'),t('queue'),async b=>{b.disabled=true;try{const endpoint=new URL(cfg.metube);endpoint.pathname=endpoint.pathname.replace(/\/$/,'')+'/add';const response=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({url:row.url,quality:quality.value,format:'mp4'})});if(!response.ok)throw Error();status.textContent=t('queued');}catch{status.textContent=t('download_failed');}finally{b.disabled=false;}}));});
 }
 function toolbar(row){
  const bar=el('div','studio-controls');bar.addEventListener('click',e=>e.stopPropagation());
  if(row.tags.length)bar.append(action('🏷',t('topics'),()=>modal(t('topics'),d=>d.append(el('p','',row.tags.join(' · '))))));
  const native=row.card.querySelector('a.bookmark');if(native)bar.append(action('☆',t('favorite'),()=>{native.click();}));
  const feed=row.card.querySelector('.website a');if(feed){const a=el('a','studio-source');a.href=feed.href;const img=feed.querySelector('img');if(img)a.append(img.cloneNode());a.append(document.createTextNode(row.source));bar.append(a);}
  if(row.video){const u=new URL(location.href);u.searchParams.set('studio_video',row.id);bar.append(external('↗',t('watch_tab'),u.href));}
  else if(safeLink(row.url))bar.append(external('↗',t('original'),row.url));
  if(row.video&&cfg.metube)bar.append(action('↓',t('download'),()=>download(row)));
  if(cfg.linkding){try{const u=new URL(cfg.linkding);if(['https:','http:'].includes(u.protocol)){u.searchParams.set('url',row.url);u.searchParams.set('title',row.title);u.searchParams.set('auto_close','');bar.append(external('🔖',t('bookmark'),u.href));}}catch{}}
  if(row.others?.length)bar.append(action('+'+row.others.length,t('duplicates'),()=>modal(t('duplicates'),d=>row.others.forEach(other=>{const p=el('p');p.append(external(other.source+' — '+other.title,t('original'),other.url));d.append(p);})))) ;
  if(row.date){const time=el('time');time.dateTime=new Date(row.date).toISOString();time.textContent=new Intl.DateTimeFormat(cfg.language,{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}).format(row.date);bar.append(time);}
  if(cfg.fullText&&kind==='news')bar.append(action(t('full_text'),t('full_text'),()=>openArticle(row)));
  return bar;
 }
 function group(){
  if(kind!=='news'||!cfg.deduplicate)return;
  const single=(new URLSearchParams(location.search).get('get')||'').startsWith('f_')||new URLSearchParams(location.search).has('search');if(single)return;
  const primaries=[];
  for(const row of rows){row.others=[];row.card.classList.remove('studio-duplicate');if(row.card.classList.contains('studio-filtered'))continue;const prior=primaries.find(p=>core.similar(p,row,cfg.threshold));if(prior){prior.others.push(row);row.card.classList.add('studio-duplicate');}else primaries.push(row);}
  for(const row of primaries){row.card.querySelector('.studio-controls')?.replaceWith(toolbar(row));}
 }
 function prepare(){
  let changed=false;
  document.querySelectorAll('#stream .flux').forEach(card=>{
   if(tracked.has(card))return;const title=card.querySelector('.flux_header a.title');if(!title)return;tracked.add(card);
   let meta={};try{meta=JSON.parse(card.querySelector('[data-studio-entry]')?.dataset.studioEntry||'{}');}catch{}
   if(!kind&&meta.kind)kind=meta.kind;if(!kind)return;
   const row={card,id:meta.id||card.dataset.entry||card.id.replace(/^flux_/,''),title:title.textContent.trim(),url:title.href,source:card.querySelector('.website a')?.textContent.trim()||'',date:Date.parse(card.querySelector('time[datetime]')?.dateTime||'')||0,tags:meta.tags||[],cover:meta.cover||card.querySelector('.thumbnail img')?.src||'',html:card.querySelector('.flux_content .text')?.innerHTML||'',video:core.videoId(title.href),others:[]};
   rows.push(row);card.classList.add('studio-card');if(core.blocked(row.title,row.tags,cfg.blocked,cfg.interested)||(cfg.hidePodcasts&&/\b(?:podcast|transcript|transkript)\b/i.test(row.title)))card.classList.add('studio-filtered');
   const head=card.querySelector('.flux_header');
   if(row.cover&&!head.querySelector('.thumbnail img')){const box=el('span','thumbnail'),img=el('img');img.alt='';img.src=row.cover;img.loading='lazy';box.append(img);head.prepend(box);}
   head.classList.add('has-thumbnail');head.after(toolbar(row));
   title.addEventListener('click',e=>{e.preventDefault();e.stopImmediatePropagation();openArticle(row);},true);
   if(row.video){card.classList.add('studio-video');const u=new URL(location.href);u.searchParams.set('studio_video',row.id);head.querySelectorAll('a.go_website,a.item-element').forEach(a=>{if(a.closest('.website,.manage'))return;a.href=u.href;a.target='_blank';a.rel='noopener noreferrer';});head.append(action('▶',t('watch'),()=>watch(row)));}
   changed=true;
  });
  if(changed)group();const target=new URLSearchParams(location.search).get('studio_video');if(target&&!root.dataset.studioWatchOpened){const row=rows.find(r=>r.id===target&&r.video);if(row){root.dataset.studioWatchOpened='1';watch(row);}}
 }
 function schedule(){if(scheduled)return;scheduled=true;requestAnimationFrame(()=>{scheduled=false;prepare();});}
 function init(){
  cfg=window.context?.extensions?.contentStudio;if(!cfg||!core)return;m=cfg.messages||{};t=key=>m[key]||key;
  const get=new URLSearchParams(location.search).get('get');kind=Object.keys(cfg.categories).find(k=>Number(cfg.categories[k])>0&&get==='c_'+cfg.categories[k])||'';
  try{theme=localStorage.getItem('content-studio-theme')||'minimal';tone=localStorage.getItem('content-studio-tone')||'night';}catch{}
  if(!['minimal','brutal','luxury','rolex-blue','nautilus'].includes(theme))theme='minimal';if(!['day','night','dark'].includes(tone))tone='night';
  appearance();root.dataset.studioKind=kind;prepare();const stream=document.getElementById('stream');if(stream)new MutationObserver(schedule).observe(stream,{childList:true,subtree:true});
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
