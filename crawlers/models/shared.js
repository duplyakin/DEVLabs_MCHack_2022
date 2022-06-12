let mongooseConnect = require('./connect.js');
let mongoose = mongooseConnect.mongoose;
let Schema = mongoose.Schema;


let taskQueueSchema = new Schema({

  action_key : String,

  status : {
    type: Number,
    default: 0, // NEW
  },

  input_data : Object,

  result_data : Object,

  credentials_id : mongoose.ObjectId,

  ack : {
    type: Number,
    default: 0,
  },

  is_queued : {
    type: Number,
    default: 0,
  },

  blocking_data : Object,

}, { collection: 'task_queue' } );


let credentialsSchema = new Schema({

  status : Number,

}, { collection: 'credentials' } );


module.exports = {
   TaskQueue : mongoose.model('TaskQueue', taskQueueSchema),
   Credentials : mongoose.model('Credentials', credentialsSchema),
}
