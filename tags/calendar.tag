<my-calendar>
    <table>
        <thead>
            <tr><td colspan="7"><button onclick={previousMonth}>&lt;-</button>{year}年{month + 1}月<button onclick={nextMonth}>-&gt;</button></td></tr>
            <tr><th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></tr>
        </thead>
        <tbody>
            <tr each={week in calendar.weeks}>
                <td each={date in week} onclick={select} class={booked: isBooked[date === '' ? 0 : date]}>{date}</td>
            </tr>
        </tbody>
    </table>
    {month + 1}月{selected.date}日
    <ul>
        <li each = {event in events}>{event.title}</li>
        <li if = {events.length === 0}>予定なし</li>
    </ul>
    <a href="./createEvent.php">イベントを作成する</a>
    <style scoped>
        .header { text-align: center; }
        table { text-align: center; }
        .booked { background: pink; }
        button {
            margin: 0 10px;
            cursor: pointer;
            border: 1px solid #444;
            padding: 2px 10px;
        }
        button:first-of-type { border-radius: 3px 0 0 3px; }
        button:last-of-type { border-radius: 0 3px 3px 0; }
        table td, table th{
            padding: 10px;
            background: #eee;
        }
    </style>
    <script>
    const calendarStore = new class CalendarStore {
        get year() {return this._year;}
        get month() {return this._month;}
        get selected() {return this._selected;}
        get calendar() {return this._calendar;}
        get bookedList() {return this._bookedList;}
        get events() {return this._events;}
        constructor() {
            const getBookedList = (weeks) => {
                const res = [];
                res[0] = false;
                for (let i = 0, _i = weeks.length; i < _i; i++) {
                    for (let j = 0, _j = weeks[i].length; j < _j; j++) {
                        if (weeks[i][j] !== '') res[weeks[i][j]] = this.getEvents(new Date(this.year, this.month, weeks[i][j])).length > 0;
                    }
                }
                return res;
            };

            riot.observable(this);
            this.__eventList__ = filterByID(filterByID(eventsStore.getEventList(), userStore.getFollowList()), userStore.getHiddenGroup(), true);
            this._year = new Date().getFullYear();
            this._month = new Date().getMonth();
            this._selected = {date: new Date().getDate()};
            this._calendar = new Calendar(this.year, this.month);
            this._events = this.getEvents(new Date(this.year, this.month, this.selected.date));
            this._bookedList = getBookedList(this.calendar.weeks);
            this.fields = ['year', 'month', 'selected', 'calendar', 'bookedList', 'events'];
            this.actionTypes = {
                changed: 'calendar_store_changed'
            };
            this.on('previousMonth', this.previousMonth.bind(this));
            this.on('nextMonth', this.nextMonth.bind(this));
            this.on('select', this.select.bind(this));
            this.on('follow unfollow show hide ' + eventsStore.actionTypes.changed, () => {
                this.__eventList__ = filterByID(filterByID(eventsStore.getEventList(), userStore.getFollowList()), userStore.getHiddenGroup(), true);
                this._events = this.getEvents(new Date(this.year, this.month, this.selected.date));
                this._bookedList = getBookedList(this.calendar.weeks);
                RiotControl.trigger(this.actionTypes.changed);
            });
        }
        previousMonth() {
            this._month--;
            if (this.month < 0) {
                this._year--;
                this._month += 12;
            }
            this._selected = {date: 1};
            this._calendar = new Calendar(this.year, this.month);
            this._events = this.getEvents(new Date(this.year, this.month, this.selected.date));
            RiotControl.trigger(this.actionTypes.changed);
        }
        nextMonth() {
            this._month++;
            if (this.month > 11) {
                this._year++;
                this._month -= 12;
            }
            this._selected = {date: 1};
            this._calendar = new Calendar(this.year, this.month);
            this._events = this.getEvents(new Date(this.year, this.month, this.selected.date));
            RiotControl.trigger(this.actionTypes.changed);
        }
        select(date) {
            if (date !== ''){
                this._selected.date = date;
                this._events = this.getEvents(new Date(this.year, this.month, this.selected.date));
                RiotControl.trigger(this.actionTypes.changed);
            }
        }
        getEvents(date) {
            return filter(this.__eventList__, date);
        }
    };
    RiotControl.addStore(calendarStore);

    const action = new class Action {
        constructor() {}
        previousMonth() {
            RiotControl.trigger('previousMonth');
        }
        nextMonth() {
            RiotControl.trigger('nextMonth');
        }
        select(date) {
            RiotControl.trigger('select', date);
        }
    };
    calendarStore.fields.forEach(item => {
        // this['year'] = calendarStore['year'];
        this[item] = calendarStore[item];
    });
    this.isBooked = calendarStore.bookedList;
    this.previousMonth = () => action.previousMonth();
    this.nextMonth = () => action.nextMonth();
    this.select = (e) => action.select(e.item.date);

    RiotControl.on(calendarStore.actionTypes.changed, () => {
        calendarStore.fields.forEach(item => {
            this[item] = calendarStore[item];
        });
        this.isBooked = calendarStore.bookedList;
        this.update();
    });
    </script>
</my-calendar>