// Define schema
const mongoose = require('mongoose');
const Schema = mongoose.Schema;
const UserSchema = new Schema(
    {
      name: {type: String, index: true, required: true},
      mobile: {type: String, index: true, required: true},
      email: {type: String, index: true, required: true},
      password: {type: String, index: true, required: true},
      last_login_at: Date,
      last_ip_address: String,
      status: Boolean,
      role: {
        type: mongoose.Schema.Types.ObjectId,
        ref: 'Role',
      },
      settings: {
        type: mongoose.SchemaTypes.Mixed,
      },
      profile:{type:String,default:''}
    },
    {timestamps: true},
);
const User= mongoose.model('user', UserSchema);
// Compile model from schema
module.exports =User;
