const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function widget() {
    const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/components/chatbot-widget.blade.php'), 'utf8');
    const script = blade.match(/<script>([\s\S]*?)<\/script>/)[1];
    const revoked = [], requests = [];
    let id = 0;
    const context = vm.createContext({ FormData, console, URL: {
        createObjectURL: () => `blob:test-${++id}`, revokeObjectURL: value => revoked.push(value),
    }, fetch: async (url, options) => {
        requests.push(options);
        return {ok: true, json: async () => ({reply: 'نتيجة تقريبية'})};
    }, window: {confirm: () => true} });
    vm.runInContext(script, context);
    const chat = context.chatbot();
    chat.$refs = { photoInput: {value: ''} };
    chat.$nextTick = fn => fn();
    return {chat, revoked, requests, context};
}

test('select and remove photo keeps upload available and releases preview', () => {
    const {chat, revoked} = widget();
    const file = new File(['image'], 'meal.png', {type: 'image/png'});
    chat.selectPhoto({target: {files: [file]}});
    assert.equal(chat.photoFile, file);
    assert.match(chat.photoPreview, /^blob:/);
    chat.clearPhoto();
    assert.equal(chat.photoFile, null);
    assert.equal(revoked.length, 1);
});

test('unsupported or oversized files never become selected', () => {
    const {chat} = widget();
    for (const file of [{type: 'image/svg+xml', size: 10}, {type: 'image/png', size: 6 * 1024 * 1024}]) {
        chat.selectPhoto({target: {files: [file], value: 'selected'}});
        assert.equal(chat.photoFile, null);
        assert.ok(chat.photoError);
    }
});

test('image only sends multipart and no history; text still works; new chat cleans URLs', async () => {
    const {chat, requests, revoked} = widget();
    chat.messages.push({sender: 'user', text: 'private context'});
    chat.selectPhoto({target: {files: [new File(['image'], 'meal.png', {type: 'image/png'})]}});
    await chat.sendMessage();
    assert.equal(requests.length, 1);
    assert.equal(requests[0].body.get('photo').name, 'meal.png');
    assert.equal(requests[0].body.has('history[0][text]'), false);
    assert.equal(requests[0].headers['Content-Type'], undefined);
    assert.equal(chat.loading, false);
    chat.inputMessage = 'نصيحة';
    await chat.sendMessage();
    assert.equal(requests[1].body.get('message'), 'نصيحة');
    assert.equal(requests[1].body.get('history[0][text]'), 'private context');
    assert.equal(requests[1].body.has('history[1][text]'), false);
    chat.newChat();
    assert.equal(chat.messages.length, 0);
    assert.equal(revoked.length, 2);
});

test('validation error is shown and loading is cleared; model HTML is escaped', async () => {
    const {chat, context} = widget();
    context.fetch = async () => ({ok: false, status: 422, json: async () => ({errors: {photo: ['صورة غير صالحة']}})});
    chat.inputMessage = 'سؤال';
    await chat.sendMessage();
    assert.equal(chat.messages.at(-1).text, 'صورة غير صالحة');
    assert.equal(chat.loading, false);
    assert.ok(chat.formatReply('<img src=x onerror=alert(1)>').includes('&lt;img'));
});

test('meal review scales portion totals and strips client-only fields before saving', async () => {
    const {chat, context} = widget();
    const msg = {mealDraft: {id: 'draft-id', date: '2026-10-31', meal_type: 'غداء', items: [{name: 'أكل', grams: 200, calories: 300, protein: 20, carbs: 40, fats: 6}]}};
    chat.openMealReview(msg);
    assert.equal(msg.review, true);
    const item = msg.mealDraft.items[0];
    item.grams = 100;
    chat.scaleMealItem(item);
    assert.equal(item.calories, 150);
    assert.equal(item.protein, 10);
    assert.ok(chat.mealTotals([item]).includes('150.0'));
    let count = 0;
    context.fetch = async (url, options) => {
        count++;
        const body = JSON.parse(options.body);
        assert.equal('_grams' in body.items[0], false);
        assert.equal(body.items[0].grams, 100);
        return {ok: true, json: async () => ({saved: true, url: '/nutrition?date=2026-10-31'})};
    };
    await chat.saveMealDraft(msg);
    await chat.saveMealDraft(msg);
    assert.equal(count, 1);
    assert.equal(msg.mealSaved, true);
    assert.equal(msg.mealSaving, false);
});

test('meal save blocks concurrent double click and supports safe retry after connection loss', async () => {
    const {chat, context} = widget();
    const msg = {mealDraft: {id: 'draft-id', date: '2026-10-31', meal_type: 'غداء', items: []}};
    let complete, count = 0;
    context.fetch = () => { count++; return new Promise(resolve => { complete = resolve; }); };
    const first = chat.saveMealDraft(msg);
    await chat.saveMealDraft(msg);
    assert.equal(count, 1);
    complete({ok: false, status: 410, json: async () => ({})});
    await first;
    assert.ok(msg.mealError.includes('صلاحية'));
    context.fetch = async () => { throw Error('connection lost'); };
    await chat.saveMealDraft(msg);
    assert.equal(msg.mealSaving, false);
    assert.equal(!!msg.mealSaved, false);
    assert.ok(msg.mealError.includes('لن تتكرر'));
});

test('reminder form cannot add a fourth slot and labels zero as appointment time', () => {
    const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/profile/partials/gym-reminder.blade.php'), 'utf8');
    const script = blade.match(/<script>([\s\S]*?)<\/script>/)[1];
    const context = vm.createContext({});
    vm.runInContext(script, context);
    const form = context.gymReminderForm([0], ['17:00'], [true]);
    form.addReminder(); form.addReminder(); form.addReminder();
    assert.equal(form.offsets.length, 3);
    assert.ok(form.daySummary(0).includes('وقت الموعد'));
    form.offsets.splice(1, 1);
    form.addReminder();
    assert.equal(new Set(form.offsets).size, 3);
});

test('mobile keyboard viewport updates and event handlers are removed on destroy', () => {
    const {chat, context} = widget();
    const listeners = new Map();
    context.window.visualViewport = {height: 460, offsetTop: 12,
        addEventListener: (key, fn) => listeners.set('viewport-' + key, fn),
        removeEventListener: key => listeners.delete('viewport-' + key)};
    context.window.addEventListener = (key, fn) => listeners.set('window-' + key, fn);
    context.window.removeEventListener = key => listeners.delete('window-' + key);
    chat.init();
    assert.equal(chat.viewportHeight, 460);
    assert.equal(chat.viewportTop, 12);
    context.window.visualViewport.height = 800;
    listeners.get('viewport-resize')();
    assert.equal(chat.viewportHeight, 800);
    chat.destroy();
    assert.equal(listeners.size, 0);
});
