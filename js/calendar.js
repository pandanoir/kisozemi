class Calendar{
    constructor(year, month) {
        this.weeks = [];
        const first = new Date(year, month, 1);
        const last = new Date(year, month + 1, 0);
        let week = [];
        for (let i = 1 - first.getDay(); i <= last.getDate(); i = 0 | i + 1) {
            week[week.length] = i > 0 ? i : '';
            if (week.length >= 7) {
                this.weeks[this.weeks.length] = week;
                week = [];
            }
        }
        if (week.length != 0) {
            while (week.length < 7) {
                week[week.length] = '';
            }
            this.weeks[this.weeks.length] = week;
            week = [];
        }
    }
}