riot.tag2('my-calendar', '<table> <thead> <tr><td colspan="7"><button onclick="{previousMonth}">&lt;-</button>{year}年{month + 1}月<button onclick="{nextMonth}">-&gt;</button></td></tr> <tr><th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></tr> </thead> <tbody> <tr each="{week in calendar.weeks}"> <td each="{date in week}" onclick="{select}" class="{booked: isBooked[date === \'\' ? 0 : date]}">{date}</td> </tr> </tbody> </table> {month + 1}月{selected.date}日 <ul> <li each="{event in events}">{event.title}</li> <li if="{events.length === 0}">予定なし</li> </ul> <script>', 'my-calendar .header,[riot-tag="my-calendar"] .header,[data-is="my-calendar"] .header{ text-align: center; } my-calendar table,[riot-tag="my-calendar"] table,[data-is="my-calendar"] table{ text-align: center; } my-calendar .booked,[riot-tag="my-calendar"] .booked,[data-is="my-calendar"] .booked{ background: pink; } my-calendar button,[riot-tag="my-calendar"] button,[data-is="my-calendar"] button{ margin: 0 10px; cursor: pointer; border: 1px solid #444; padding: 2px 10px; } my-calendar button:first-of-type,[riot-tag="my-calendar"] button:first-of-type,[data-is="my-calendar"] button:first-of-type{ border-radius: 3px 0 0 3px; } my-calendar button:last-of-type,[riot-tag="my-calendar"] button:last-of-type,[data-is="my-calendar"] button:last-of-type{ border-radius: 0 3px 3px 0; } my-calendar table td,[riot-tag="my-calendar"] table td,[data-is="my-calendar"] table td,my-calendar table th,[riot-tag="my-calendar"] table th,[data-is="my-calendar"] table th{ padding: 10px; background: #eee; }', '', function(opts) {
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

        self[item] = calendarStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
    });
    this.isBooked = calendarStore.getBookedList();

    this.previousMonth = function() {
        action.previousMonth();
    }.bind(this)
    this.nextMonth = function() {
        action.nextMonth();
    }.bind(this)
    this.select = function(e) {
        action.select(e.item.date);
    }.bind(this)
    RiotControl.on(calendarStore.actionTypes.changed, function() {
        calendarStore.fields.forEach(function(item) {
            self[item] = calendarStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
        });
        self.isBooked = calendarStore.getBookedList();
        self.update();
    });
});