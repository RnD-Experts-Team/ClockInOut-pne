window.loadMoreNotifications=B;window.handleDatabaseNotificationClick=k;window.markNotificationAsRead=L;window.viewMaintenanceRequest=E;window.markAllAsRead=C;window.closeToast=g;const c={NORMAL:"normal",LOADING:"loading",ERROR:"error",HIDDEN:"hidden"};document.addEventListener("DOMContentLoaded",()=>{const t=document.getElementById("notifications-button"),e=document.getElementById("mobile-notifications-button");t?t.addEventListener("click",w):console.error("❌ Desktop notifications button not found!"),e?e.addEventListener("click",w):console.error("❌ Mobile notifications button not found!")});function m(t,e=null){const o=document.getElementById("load-more-btn"),n=document.getElementById("load-more-icon"),i=document.getElementById("load-more-text");if(!o||!n||!i){console.warn("⚠️ Load More button elements not found");return}switch(o.classList.remove("opacity-50","cursor-not-allowed","bg-red-50","text-red-600","hover:bg-red-100"),o.disabled=!1,o.setAttribute("data-state",t),t){case c.LOADING:o.disabled=!0,o.classList.add("opacity-50","cursor-not-allowed"),n.innerHTML='<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>',i.textContent=e||"Loading more...";break;case c.ERROR:o.classList.add("bg-red-50","text-red-600","hover:bg-red-100"),n.innerHTML="⚠️",i.textContent=e||"Error loading - Click to retry";break;case c.NORMAL:o.classList.add("text-orange-600","hover:text-orange-800","hover:bg-orange-50"),n.innerHTML="📖",i.textContent=e||"Load More Notifications";break;case c.HIDDEN:M();break}}function M(){const t=document.getElementById("load-more-container");t&&(t.remove(),console.log("➖ Load More button removed"))}let r=0,u=[],f=1,y=!0;const x=new Pusher(document.querySelector('meta[name="pusher-key"]').getAttribute("content"),{cluster:document.querySelector('meta[name="pusher-cluster"]').getAttribute("content"),forceTLS:!0});window.pusherConfig.isAdmin&&(x.subscribe("maintenance-notifications").bind("maintenance.request.received",function(e){console.log("🔔 New maintenance notification:",e),A(e),$(e),q()}),x.connection.bind("connected",function(){console.log("✅ Connected to notification system")}));async function h(t=1,e=!1){var o;try{console.log("🔍 Attempting to load notifications...",{page:t,append:e,url:`/notifications?page=${t}&per_page=3`});const n=await fetch(`/notifications?page=${t}&per_page=3`,{method:"GET",headers:{Accept:"application/json","Content-Type":"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":((o=document.querySelector('meta[name="csrf-token"]'))==null?void 0:o.getAttribute("content"))||""},credentials:"same-origin"});console.log("📡 Response status:",n.status),console.log("📡 Response headers:",Object.fromEntries(n.headers.entries()));const i=n.headers.get("content-type");if(console.log("📄 Content-Type:",i),!i||!i.includes("application/json")){console.error("❌ Response is not JSON!");const s=await n.text();if(console.error("📄 Response text (first 500 chars):",s.substring(0,500)),s.includes("<!DOCTYPE"))throw new Error("Server returned HTML instead of JSON - likely an authentication or routing issue")}const a=await n.json();if(console.log("✅ JSON Response received:",a),e)u=[...u,...a.notifications||[]];else{u=a.notifications||[];const s=document.getElementById("notifications-list");s&&(s.innerHTML="")}r=a.unread_count||0,y=a.has_more||!1,d(),N(a.notifications||[],e),(a.notifications||[]).length===0&&!e&&I(),console.log(`📊 Loaded ${(a.notifications||[]).length} notifications (page ${t})`)}catch(n){console.error("❌ Error loading notifications:",n),console.error("📄 Full error details:",{name:n.name,message:n.message,stack:n.stack}),R()}}function N(t,e=!1){const o=document.getElementById("notifications-list");o&&(e||(o.innerHTML=""),t.forEach(n=>{const i=b(n);o.insertAdjacentHTML("beforeend",i)}),y&&!document.getElementById("load-more-btn")&&o.insertAdjacentHTML("afterend",`
        <div class="px-4 py-3 border-t border-orange-100">
            <button id="load-more-btn" onclick="loadMoreNotifications()"
                    class="w-full text-center text-sm text-orange-600 hover:text-orange-800 font-medium py-2 hover:bg-orange-50 rounded-lg transition-colors">
                📖 Load More Notifications
            </button>
        </div>
    `))}function b(t){var v;const e=t.type==="urgent_request",o=e?"border-l-4 border-red-500 bg-red-50":"border-l-4 border-orange-500 bg-orange-50",n=e?"🚨":"🔧",i=t.read_at!==null,a=_(t.created_at),s=t.maintenance_request,l=((v=s==null?void 0:s.store)==null?void 0:v.name)||"Unknown Store",T=(s==null?void 0:s.equipment_with_issue)||"Unknown Equipment";return s!=null&&s.urgency_level,`
    <div class="notification-item relative p-4 hover:bg-gray-50 transition-colors duration-200 cursor-pointer ${o} ${i?"opacity-60":""}"
         onclick="handleDatabaseNotificationClick(${t.id}, ${t.maintenance_request_id}, this)">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <span class="text-2xl">${n}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 mb-1">
                    ${t.message}
                </p>
                <div class="text-xs text-gray-600 space-y-1">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                        </svg>
                        <span>${l}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996-.608 2.296-.07 2.572 1.065z"></path>
                        </svg>
                        <span>${T}</span>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-xs text-gray-400">${a}</span>
                </div>
                <div class="flex items-center space-x-2 mt-3 pt-2 border-t border-gray-100">
                    <button onclick="event.stopPropagation(); viewMaintenanceRequest(${t.maintenance_request_id})"
                            class="flex-1 inline-flex items-center justify-center px-3 py-1 bg-orange-600 text-white text-xs font-medium rounded-md hover:bg-orange-700 transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        View Details
                    </button>
                    ${i?`
                    <span class="px-3 py-1 text-xs text-green-600 bg-green-50 rounded-md">
                        ✓ Read
                    </span>
                    `:`
                    <button onclick="event.stopPropagation(); markNotificationAsRead(${t.id}, this.closest('.notification-item'))"
                            class="px-3 py-1 text-xs text-gray-600 hover:text-gray-800 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Mark Read
                    </button>
                    `}
                </div>
            </div>
        </div>
        ${i?'<div class="absolute top-2 right-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>':""}
    </div>
`}function w(){const t=document.getElementById("notifications-dropdown");if(!t){console.error("❌ Notifications dropdown not found!");return}t.style.display==="none"||t.style.display===""?(t.style.display="block",t.style.visibility="visible",t.style.opacity="1",t.style.transform="translateY(0)",t.style.position="absolute",t.style.top="100%",t.style.right="0",t.style.zIndex="9999",console.log("✅ Dropdown opened, loading notifications..."),f=1,h(),setTimeout(()=>document.addEventListener("click",p),100)):(t.style.display="none",document.removeEventListener("click",p),console.log("✅ Dropdown closed"))}function k(t,e,o){console.log(`🖱️ Notification clicked: ${t}, maintenanceRequestId: ${e}, element:`,o),o&&!o.classList.contains("opacity-60")&&L(t,o),E(e)}async function L(t,e){try{(await fetch(`/notifications/${t}/read`,{method:"PATCH",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"Content-Type":"application/json"}})).ok&&(e.classList.add("opacity-60"),e.querySelector(".absolute.top-2.right-2")||(e.innerHTML+='<div class="absolute top-2 right-2"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>'),r=Math.max(0,r-1),d(),console.log(`✅ Notification ${t} marked as read`))}catch(o){console.error("❌ Error marking notification as read:",o)}}async function C(){try{(await fetch("/notifications/mark-all-read",{method:"PATCH",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').getAttribute("content"),"Content-Type":"application/json"}})).ok&&(document.querySelectorAll(".notification-item").forEach(e=>{e.classList.add("opacity-60")}),r=0,d(),console.log("✅ All notifications marked as read"))}catch(t){console.error("❌ Error marking all notifications as read:",t)}}function B(){f++;const t=document.getElementById("load-more-btn");t&&m(c.LOADING,"Loading more..."),h(f,!0).then(()=>{t&&m(c.NORMAL)}).catch(e=>{console.error("❌ Error loading more notifications:",e),t&&m(c.ERROR,"Error loading - Click to retry")})}function A(t){console.log("📝 Adding real-time notification:",t),r++;const e={id:t.id,message:t.message,type:t.type,read_at:null,created_at:t.created_at,maintenance_request_id:t.maintenance_request_id,maintenance_request:{id:t.maintenance_request_id,store:{name:t.store_name},equipment_with_issue:t.equipment,urgency_level:t.urgency_level}};u.unshift(e),d();const o=document.getElementById("notifications-list");if(o&&!document.getElementById("no-notifications")){const n=b(e);o.insertAdjacentHTML("afterbegin",n);const i=o.querySelectorAll(".notification-item");i.length>10&&i[i.length-1].remove()}}function $(t){console.log("📋 Toast notification received:",t);const e=document.getElementById("toast-container");if(!e){console.error("❌ Toast container not found!");return}const o=`toast-${Date.now()}`,n=t.is_urgent,i=n?"🚨":"🔧",a=t.maintenance_request_id||t.id||null;a||console.warn("⚠️ No maintenance_request_id found in toast data, using fallback or null");const s=`
        <div id="${o}" class="relative max-w-md w-full bg-gradient-to-r ${n?"from-red-500 to-red-600":"from-orange-500 to-orange-600"} shadow-2xl rounded-xl pointer-events-auto transform transition-all duration-500 translate-x-full hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent rounded-xl"></div>
            <div class="relative p-4 text-white">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 relative">
                        <div class="w-10 h-10 ${n?"bg-red-400":"bg-orange-400"} rounded-full flex items-center justify-center animate-pulse">
                            <span class="text-xl">${i}</span>
                        </div>
                        <div class="absolute inset-0 ${n?"bg-red-400":"bg-orange-400"} rounded-full animate-ping opacity-20"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-sm ${n?"text-red-100":"text-orange-100"}">
                                ${n?"URGENT":"NEW REQUEST"}
                            </h4>
                            <button onclick="event.stopPropagation(); closeToast('${o}')"
                                    class="text-white/70 hover:text-white transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-white font-medium text-sm mb-2 leading-tight">
                            ${t.message}
                        </p>
                        <div class="space-y-1">
                            <div class="flex items-center text-xs text-white/90">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                                </svg>
                                <span class="font-medium">${t.store_name}</span>
                            </div>
                            <div class="flex items-center text-xs text-white/90">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996-.608 2.296-.07 2.572 1.065z"></path>
                                </svg>
                                <span>${t.equipment}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-t border-white/20">
                            <button onclick="handleDatabaseNotificationClick(${t.id}, ${a}, null); closeToast('${o}')"
                                    class="w-full bg-white/20 hover:bg-white/30 text-white text-xs font-medium py-2 px-3 rounded-lg transition-all duration-200 backdrop-blur-sm">
                                👁️ View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 bg-white/30 rounded-b-xl">
                <div class="h-full bg-white rounded-b-xl animate-toast-progress"></div>
            </div>
        </div>
    `;e.insertAdjacentHTML("beforeend",s),setTimeout(()=>{const l=document.getElementById(o);l&&(l.classList.remove("translate-x-full"),l.onclick=()=>{k(t.id,a,null),g(o)})},100),setTimeout(()=>{g(o)},8e3)}function g(t){const e=document.getElementById(t);e&&(e.classList.add("translate-x-full"),setTimeout(()=>{e.remove()},300))}function d(){const t=document.getElementById("notification-badge"),e=document.getElementById("mobile-notification-badge"),o=document.getElementById("notification-count"),n=document.getElementById("notification-pulse");r>0?(t&&(t.textContent=r,t.classList.remove("hidden")),e&&(e.textContent=r,e.classList.remove("hidden")),o&&(o.textContent=`${r} new`),n&&n.classList.remove("hidden")):(t&&t.classList.add("hidden"),e&&e.classList.add("hidden"),o&&(o.textContent="0 new"),n&&n.classList.add("hidden"))}function _(t){const e=new Date(t),n=Math.floor((new Date-e)/1e3);return n<60?"Just now":n<3600?`${Math.floor(n/60)}m ago`:n<86400?`${Math.floor(n/3600)}h ago`:`${Math.floor(n/86400)}d ago`}function I(){const t=document.getElementById("notifications-list");t&&(t.innerHTML=`
        <div class="px-4 py-8 text-center text-gray-500" id="no-notifications">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h5v14z"></path>
            </svg>
            <p class="text-sm">No notifications yet</p>
            <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
        </div>
    `)}function R(){const t=document.getElementById("notifications-list");t&&(t.innerHTML=`
        <div class="px-4 py-8 text-center text-red-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <p class="text-sm">Error loading notifications</p>
            <button onclick="loadNotifications()" class="text-xs text-red-600 hover:text-red-800 mt-2">Try Again</button>
        </div>
    `)}function q(){try{const t=new(window.AudioContext||window.webkitAudioContext),e=t.createOscillator(),o=t.createGain();e.connect(o),o.connect(t.destination),e.frequency.setValueAtTime(800,t.currentTime),e.frequency.setValueAtTime(600,t.currentTime+.1),e.type="sine",o.gain.setValueAtTime(.1,t.currentTime),o.gain.exponentialRampToValueAtTime(.01,t.currentTime+.3),e.start(t.currentTime),e.stop(t.currentTime+.3)}catch(t){console.log("Could not play notification sound:",t)}}function E(t){window.location.href="/maintenance-requests/"+t}function p(t){const e=document.getElementById("notifications-dropdown"),o=t.target.closest('button[onclick*="toggleNotifications"]');e&&!e.contains(t.target)&&!o&&(e.style.display="none",document.removeEventListener("click",p))}document.addEventListener("DOMContentLoaded",function(){console.log("🚀 Enhanced notification system initialized"),h(),d(),setTimeout(()=>{console.log("🧪 Running notification system health check...");const t=document.getElementById("notifications-dropdown"),e=document.getElementById("toast-container");console.log("✅ Dropdown exists:",!!t),console.log("✅ Toast container exists:",!!e),console.log("📊 Current notification count:",r)},3e3)});
