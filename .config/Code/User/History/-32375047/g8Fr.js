const { func } = require('joi');
const Joi = require('joi');
const Role = require('../models/Role.model');
const User = require('../models/User.model');
const {hash} = require('../utils/hashing.utils');
const {sendErrorResponse, sendSuccessResponse} = require('../utils/sendResponse.utils');
/**
 * Description
 * @param {any} req
 * @param {any} res
 * @return {any}
 */
const signup = async (req, res) => {
  try {
    console.log(req.body);
    const body = req.body || {};
    const schema = Joi.object({
      name: Joi.string().min(2).required(),
      mobile: Joi.string().min(10).max(13)
          .pattern(/^(?:(?:\+|0{0,2})91(\s*[\-]\s*)?|[0]?)?[789]\d{9}$/).external(async (value, helper)=>{
            console.log(value);
            const user= await User.findOne({mobile: value}).exec();
            let details=[
                {
              message: '"mobile" allready exist [ref:email]',
                  path: 'mobile'
                 }]
              
            if(user) {
              throw new Joi.ValidationError('mobile allready exist',details);
            }
          }),
      password: Joi.string(),
      confirm_password: Joi.ref('password'),
      email: Joi.string().email().external(async (value, helper)=>{
        console.log(value);
        const user= await User.findOne({email: value}).exec();
        let details=[
            {
          message: '"email" allready exist [ref:email]',
              path: 'email'
             }]
          
        if(user) {
          throw new Joi.ValidationError('email allready exist',details);
        }
      }).email(),
    });
    const value = await schema.validateAsync(body);
    const settings = {
      notification: {
        push: true,
        email: true,
      },
    };
    const role=await Role.findOne({'name': 'user'}).exec();
    console.log(role);
    user = await User.create({
      name: value.name,
      email: value.email,
      mobile: value.mobile,
      password: hash(value.password),
      role: role._id,
      settings,
    });
    return sendSuccessResponse(res, 201, {
      user: {
        id: user.id,
        name: user.name,
        email: user.email,
      },
    }, 'Account created successfully');
  } catch (error) {
    console.error(error);
    return sendErrorResponse(res, res.details?422:500,
        `Could not perform operation at
         this time, kindly try again later.`, error);
  }
};
const login =(req,res)=>{

}
module.exports = {
  signup,
};
