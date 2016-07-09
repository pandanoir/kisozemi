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
    var calendarStore = new function() {
        var self = this;
        var eventList = filterByID(filterByID(eventsStore.getEventList(), userStore.getFollowList()), userStore.getHiddenGroup(), true);
        
        riot.observable(this);
        this.year = new Date().getFullYear();
        this.month = new Date().getMonth();
        this.selected = {date: new Date().getDate()};
        this.calendar = new Calendar(this.year, this.month);
        this.events = getEvents(new Date(this.year, this.month, this.selected.date));
        this.bookedList = getBookedList(this.calendar.weeks);
        this.fields = ['year', 'month', 'selected', 'calendar', 'bookedList', 'events'];
        this.previousMonth = function() {
            this.month--;
            if (this.month < 0) {
                this.year--;
                this.month += 12;
            }
            this.selected = {date: 1};
            this.calendar = new Calendar(this.year, this.month);
            this.events = getEvents(new Date(this.year, this.month, this.selected.date));
            RiotControl.trigger(this.actionTypes.changed);
        };
        this.nextMonth = function() {
            this.month++;
            if (this.month > 11) {
                this.year++;
                this.month -= 12;
            }
            this.selected = {date: 1};
            this.calendar = new Calendar(this.year, this.month);
            this.events = getEvents(new Date(this.year, this.month, this.selected.date));
            RiotControl.trigger(this.actionTypes.changed);
        };
        this.select = function(date) {
            if (date !== ''){
                this.selected.date = date;
                this.events = getEvents(new Date(this.year, this.month, this.selected.date));
                RiotControl.trigger(this.actionTypes.changed);
            }
        };
        this.fields.forEach(function(item) {
            // self['getYear'] = function() {return self['year']};
            self['get' + item.charAt(0).toUpperCase() + item.slice(1)] = function() {return self[item]};
        });
        this.actionTypes = {
            changed: 'calendar_store_changed'
        };
        this.on('previousMonth', this.previousMonth.bind(this));
        this.on('nextMonth', this.nextMonth.bind(this));
        this.on('select', this.select.bind(this));
        this.on('follow unfollow show hide ' + eventsStore.actionTypes.changed, function() {
            eventList = filterByID(filterByID(eventsStore.getEventList(), userStore.getFollowList()), userStore.getHiddenGroup(), true);
            self.events = getEvents(new Date(self.year, self.month, self.selected.date));
            self.bookedList = getBookedList(self.calendar.weeks);
            RiotControl.trigger(self.actionTypes.changed);
        });
        function getEvents(date) {
            return filter(eventList, date);
        }
        function getBookedList(weeks) {
            var res = [];
            res[0] = false;
            for (var i = 0, _i = weeks.length; i < _i; i++) {
                for (var j = 0, _j = weeks[i].length; j < _j; j++) {
                    if (weeks[i][j] !== '') res[weeks[i][j]] = getEvents(new Date(self.year, self.month, weeks[i][j])).length > 0;
                }
            }
            return res;
        }
    };
    RiotControl.addStore(calendarStore);
    var action = new function() {
        this.previousMonth = function() {
            RiotControl.trigger('previousMonth');
        };
        this.nextMonth = function() {
            RiotControl.trigger('nextMonth');
        };
        this.select = function(date) {
            RiotControl.trigger('select', date);
        };
    };
    var self = this;
    calendarStore.fields.forEach(function(item) {
        // self['year'] = calendarStore['getYear']();
        self[item] = calendarStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
    });
    this.isBooked = calendarStore.getBookedList();

    previousMonth() {
        action.previousMonth();
    }
    nextMonth() {
        action.nextMonth();
    }
    select(e) {
        action.select(e.item.date);
    }
    RiotControl.on(calendarStore.actionTypes.changed, function() {
        calendarStore.fields.forEach(function(item) {
            self[item] = calendarStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
        });
        self.isBooked = calendarStore.getBookedList();
        self.update();
    });
</my-calendar>