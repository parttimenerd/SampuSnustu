pingPongStore = {}
PingPong = function(canvas_id){
    pingPongStore[canvas_id] = this;
    this.canvas = document.getElementById(canvas_id);
    this.context = null;
    this.width = this.canvas.width;
    this.height = this.canvas.height;
    this.level = 0;
    this.px_per_s = 100;
    this.stick = new Stick(this.height / 2, this);
    this.ball = new Ball(this.width / 2, this.height / 2, this);
    this.intervall = 10; //in ms, time between two paint actions
    this.user_interaction = false;
    this.front_color = "darkgrey";
    this.font_color = "dimgrey";
    this.paint = function(){
        if (this.context == null){
            this.canvas = document.getElementById(canvas_id);
            this.canvas.onmousemove = function(event){
                pingPongStore[canvas_id].stick.setY(event.layerY - 80);
                pingPongStore[canvas_id].user_interaction = true;
		//console.log(pingPongStore[canvas_id].stick.y1);
            }
            this.canvas.onmouseout = function(event){
                pingPongStore[canvas_id].user_interaction = false;
            //console.log(pingPongStore[canvas_id].stick.y1);
            }
            this.height = this.canvas.height;
            this.context = this.canvas.getContext("2d");
        }
        this.ball.update(this.stick);
        this.canvas.width = this.canvas.width;
        this.context.fillStyle = "#030311";
        this.context.fillRect(0, 0, this.width, this.height);
        this.context.fillStyle = this.front_color;
        this.context.font = this.height / 6 + "px 'Atari Classic Chunky'";
        var text = "Level " + this.level;
        this.context.fillText(text, (this.width - this.context.measureText(text).width) / 2, this.height / 5);
        this.ball.draw(this.context);
        this.stick.draw(this.context);
        setTimeout("pingPongStore['" + this.canvas.id + "'].paint()", this.intervall)
    }
}
            
Stick = function(_y, pingpong){
    this.pingpong = pingpong;
    this.width = 4;    //in px
    this.defheight = 40;
    this.height_decr_fac = 0.1;
    this.height = this.defheight * Math.pow(this.height_decr_fac, pingpong.level == 0 ? 0 : Math.ceil((pingpong.level / 2) - 1));
    this.h2 = this.height / 2;
    this.defy = _y;
    this.y = _y;
    this.y1 = _y - this.h2;
    this.y2 = _y + this.h2;
    this.draw = function(context){
        context.fillRect(0, this.y1, this.width, this.height);
    //console.log("dra " + this.y1 + " | " + this.y);
    }
    this.getAngle = function(ball){
        if (ball.y == this.y){
            if (pingpong.level % 2 != 0 && pingpong.user_interaction && ball.level_plays == 0){
                var diff = this.height * this.height_decr_fac;
                this.height -= diff;
                h2 = this.height / 2;
                this.y -= diff/2;
                this.y1 += diff/2;
                this.y2 -= diff/2;
            }
            return {
                x: 1, 
                y: 0
            };
        } else if (ball.y > this.y1 - ball.radius + 1 && ball.y < this.y2 + ball.radius - 1){
            var val = (ball.y - (this.y1 - ball.radius + 1)) / (this.height + (2 * ball.radius) + 2) * Math.PI;
            if (pingpong.level % 2 != 0 && pingpong.user_interaction && ball.level_plays == 0){
                var diff = this.height * this.height_decr_fac;
                this.height -= diff;
                h2 = this.height / 2;
                this.y -= diff/2;
                this.y1 += diff/2;
                this.y2 -= diff/2;
            }
            return {
                x: Math.abs(Math.sin(val)), 
                y: Math.abs(Math.cos(val))
            };
        }
        this.height = this.defheight;
        h2 = this.height / 2;
        this.y = this.defy;
        this.y1 = this.defy - h2;
        this.y2 = this.defy + h2;
        return -1;
    }
    this.move = function(dist){
        this.setY(this.y + dist);
    }
    this.setY = function(y){
        this.y = y * 1.0;
        if (this.y + this.h2 > pingpong.height){
            this.y = pingpong.height - this.h2;
        } else if (this.y < this.h2){
            this.y = this.h2;
        }
        this.y1 = this.y - this.h2;
        //console.log("set " + this.y1 + " | " + this.y);
        this.y2 = this.y + this.h2;
    }
}
Ball = function(x, y, pingpong){
    this.x = x;
    this.y = y;
    this.def_x = x;
    this.def_y = y;
    this.def_radius = 7;
    this.radius_decr_fac = 0.13;
    this.radius = this.def_radius * Math.pow(this.radius_decr_fac, pingpong.level == 0 ? 0 : Math.ceil((pingpong.level / 2) - 1));
    this.color = "darkgrey";
    this.pingpong = pingpong;
    this.last_time = Date.now();
    this.v_incr_fac = 0.15;
    var fac = Math.pow(this.v_incr_fac, Math.ceil(pingpong.level / 2)) + 1;
    this.def_y_v = 0;
    this.def_x_v = pingpong.width / 6;
    this.y_v = this.def_y_v * fac;      //px per s
    this.x_v = this.def_x_v * fac;
    this.x_stick_hit = false;
    this.first_hit = true;
    this.plays_per_level = 2;
    this.level_plays = 0;
    this.draw = function(context){
        //context.beginPath();
        context.fillRect(this.x - this.radius, this.y - this.radius, this.radius * 2, this.radius * 2);
    //context.arc(this.x, this.y, this.radius, 0, 360, false);
    //context.fill();
    }
    this.update = function(stick){
        var tdiff = (Date.now() - this.last_time) / 1000;
        var x_val = this.x + (this.x_v * tdiff / 2);
        var y_val = this.y + (this.y_v * tdiff / 2);
        if (x_val - this.radius <= stick.width && !this.x_stick_hit){
            var angle = stick.getAngle(this);
            if (angle == -1){
                this.x = this.def_x;
                this.y = this.def_y;
                this.radius = this.def_radius;
                this.x_v = this.def_x_v * fac;
                this.y_v = this.def_y_v * fac;
                pingpong.level = 0;
                return;
            }
            if (pingpong.user_interaction && this.level_plays >= this.plays_per_level){
                this.level_plays = 0;
                pingpong.level++;
            } else if (pingpong.user_interaction){
                this.level_plays++;
            }
            var square = Math.sqrt(Math.pow(this.x_v, 2) + Math.pow(this.y_v, 2))
            this.x_v = square * angle.x;
            this.y_v = square * angle.y;
            if (pingpong.level % 2 == 0 && pingpong.user_interaction && this.level_plays == 0){
                this.x_v *= (this.v_incr_fac + 1);
                this.y_v *= (this.v_incr_fac + 1);
            }
            if (pingpong.level % 2 != 0 && pingpong.user_interaction && this.level_plays == 0){
                this.radius -= this.radius * this.radius_decr_fac;
            }
            this.x_stick_hit = true;
        } else {
            if (x_val + this.radius > pingpong.width){
                this.x_v = -Math.abs(this.x_v);
                this.x_stick_hit = false;
            }
            if (Math.round(y_val + this.radius) > pingpong.height){
                this.y_v = -Math.abs(this.y_v);
            } else if (Math.round(y_val - this.radius) < 0){
		this.y_v = Math.abs(this.y_v);
	    }
        }
        this.x += this.x_v * tdiff;
        this.y += this.y_v * tdiff;
        this.last_time = Date.now();
    }
}

