<my-user-info>
    {name}(@{screenName})<a href="./logout?token={generate_token}">ログアウト</a><br>
    フォローしているグループ
    <ul>
        <li each={id in followList}>
            {groups[id].name}<span if={id >= 10}>(@{groups[id].screenName})</span>
            <button onclick={unfollow} if={id >= 10}>フォロー解除</button>
            <button onclick={hiddenGroup.indexOf(id) === -1 ? hide : show}>{hiddenGroup.indexOf(id) === -1 ? '非' : ''}表示にする</button>
        </li>
    </ul>
    <button onclick={hideAll}>すべて非表示にする</button>
    <button onclick={showAll}>すべて表示する</button>
    <style scoped>
    </style>
    <script>
    this.generate_token = opts.generateToken;
    this.name = userStore.name;
    this.screenName = userStore.screenName;
    this.followList = userStore.followList;
    this.hiddenGroup = userStore.hiddenGroup;
    this.groups = groupsStore.groupList;
    RiotControl.on('follow unfollow hide show hide-all show-all' + groupsStore.actionTypes.changed, () => {
        this.followList = userStore.followList;
        this.hiddenGroup = userStore.hiddenGroup;
        this.update();
    });
    RiotControl.on(groupsStore.actionTypes.changed, () => {
        this.name = userStore.name;
        this.screenName = userStore.screenName;
        this.followList = userStore.followList;
        this.hiddenGroup = userStore.hiddenGroup;
        this.groups = groupsStore.groupList;
        this.update();
    });
    const action = new class Action {
        constructor() {}
        unfollow(id) {
            RiotControl.trigger('unfollow', id);
        }
        hide(id) {
            RiotControl.trigger('hide', id);
        }
        show(id) {
            RiotControl.trigger('show', id);
        }
        hideAll() {
            RiotControl.trigger('hide-all');
        }
        showAll() {
            RiotControl.trigger('show-all');
        }
    };
    this.unfollow = (e) => {
        action.unfollow(e.item.id);
    };
    this.hide = (e) => {
        action.hide(e.item.id);
    };
    this.show = (e) => {
        action.show(e.item.id);
    };
    this.hideAll = (e) => {
        action.hideAll();
    };
    this.showAll = (e) => {
        action.showAll();
    };
</my-user-info>
