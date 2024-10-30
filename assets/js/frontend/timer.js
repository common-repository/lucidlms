/**
 * class Timer.
 * Uses jQuery DOM object with string in format hh:mm:ss to create timer.
 *
 * @param htmlTimer
 * @param callbackFunc
 * @param onTimerEndFunc
 * @param delayMs
 * @constructor
 */
var Timer = function (htmlTimer, callbackFunc, onTimerEndFunc, delayMs) {

	delayMs = typeof delayMs !== 'undefined' ? delayMs : 1000;

	this.delayMs = delayMs;
	this.htmlTimer = htmlTimer;
	this.callbackFunc = callbackFunc;
	this.onTimerEndFunc = onTimerEndFunc;
	this.timerState = 'new';
};

/**
 * Timer: start function
 */
Timer.prototype.start = function () {
	if (this.tmr) return;

	this.timerState = 'running';
	this.repeat();
};

/**
 * Timer: repeat function
 */
Timer.prototype.repeat = function () {
	var self = this;
	this.tmr = setTimeout(function () {
		self._handleTmr();
		if (self.tmr) {
			self.repeat();
		}
	}, this.delayMs);
};

/**
 * Timer: stop function
 */
Timer.prototype.stop = function () {
	if (!this.tmr) return;

	clearTimeout(this.tmr);
	this.tmr = null;
	this.timerState = 'standby';
};

/**
 * Timer: main function (uses jQuery)
 */
Timer.prototype._initFunc = function () {
	var time = this.htmlTimer.html();
	var ss = time.split(":");
	var dt = new Date();
	dt.setHours(ss[0]);
	dt.setMinutes(ss[1]);
	dt.setSeconds(ss[2]);

	var dt2 = new Date(dt.valueOf() - this.delayMs);
	var temp = dt2.toTimeString().split(" ");
	var ts = temp[0].split(":");

	this.htmlTimer.html(ts[0] + ":" + ts[1] + ":" + ts[2]);

	if ((ts[0] === "00") && (ts[1] === "00") && (ts[2] === "00")) {
		this.stop();
		if (this.onTimerEndFunc) {
			this.onTimerEndFunc();
		}
	}
};

/**
 * Timer: handle main and callback functions
 */
Timer.prototype._handleTmr = function () {
	this._initFunc();
	if (this.callbackFunc) {
		this.callbackFunc();
	}
};