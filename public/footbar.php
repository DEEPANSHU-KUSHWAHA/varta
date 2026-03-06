<div class="footbar animated-footbar">
    <button class="emoji-btn" title="Emoji Picker">😊</button>
    <div class="emoji-picker">
        <span>😀</span> <span>😂</span> <span>😍</span> <span>😎</span> <span>🥳</span> <span>😇</span> <span>🤔</span> <span>😢</span> <span>😡</span> <span>👍</span> <span>👀</span>
    </div>
    <input type="text" class="chat-input" placeholder="Type a message..." />
    <button class="send-btn">Send</button>
</div>



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