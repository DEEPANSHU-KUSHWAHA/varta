<div class="footbar animated-footbar">
    <button class="emoji-btn" title="Emoji Picker">😊</button>
    <div class="emoji-picker" style="display:none;">
        <span>😀</span> <span>😂</span> <span>😍</span> <span>😎</span> <span>🥳</span> <span>😇</span> <span>🤔</span> <span>😢</span> <span>😡</span> <span>👍</span> <span>👀</span>
    </div>
    <input type="text" class="chat-input" placeholder="Type a message..." />
    <button class="send-btn">Send</button>
</div>

<style>
.footbar.animated-footbar {
    position: fixed;
    left: 0;
    bottom: 0;
    width: 100vw;
    background: rgba(30, 42, 100, 0.92);
    box-shadow: 0 -8px 32px 0 rgba(31, 38, 135, 0.18);
    backdrop-filter: blur(8px);
    border-radius: 18px 18px 0 0;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 32px;
    z-index: 100;
    animation: fadeInScale 0.7s cubic-bezier(.68,-0.55,.27,1.55);
}
.footbar .emoji-btn {
    font-size: 1.5em;
    background: none;
    border: none;
    cursor: pointer;
    margin-right: 8px;
    transition: transform 0.2s;
}
.footbar .emoji-btn:hover {
    transform: scale(1.2) rotate(-10deg);
}
.footbar .emoji-picker {
    position: absolute;
    bottom: 60px;
    left: 40px;
    background: #fff;
    color: #222;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.12);
    padding: 10px 18px;
    font-size: 1.3em;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    z-index: 200;
    animation: fadeInScale 0.4s;
}
.footbar .chat-input {
    flex: 1;
    font-size: 1.1em;
    padding: 10px 16px;
    border-radius: 8px;
    border: 1px solid #3949ab;
    margin-right: 8px;
    outline: none;
    transition: border 0.2s;
}
.footbar .chat-input:focus {
    border: 1.5px solid #ffd600;
}
.footbar .send-btn {
    background: linear-gradient(90deg, #3949ab 0%, #00c6ff 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 22px;
    font-size: 1.1em;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: background 0.2s, transform 0.2s;
}
.footbar .send-btn:hover {
    background: linear-gradient(90deg, #ffd600 0%, #00c6ff 100%);
    transform: scale(1.08);
}
</style>

<script>
const emojiBtn = document.querySelector('.emoji-btn');
const emojiPicker = document.querySelector('.emoji-picker');
const chatInput = document.querySelector('.chat-input');
if (emojiBtn && emojiPicker) {
    emojiBtn.addEventListener('click', () => {
        emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'flex' : 'none';
    });
    emojiPicker.querySelectorAll('span').forEach(emoji => {
        emoji.addEventListener('click', () => {
            chatInput.value += emoji.textContent;
            emojiPicker.style.display = 'none';
            chatInput.focus();
        });
    });
}
</script>