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
    <style scoped>
    </style>
    <script>
    RiotControl.on('follow unfollow hide show ' + groupsStore.actionTypes.changed, () => {
    this.generate_token = opts.generateToken;
    this.name = userStore.name;
    this.screenName = userStore.screenName;
    this.followList = userStore.followList;
    this.hiddenGroup = userStore.hiddenGroup;
    this.groups = groupsStore.groupList;
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
</my-user-info>
