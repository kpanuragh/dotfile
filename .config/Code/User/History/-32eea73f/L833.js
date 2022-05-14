/**
 * Send error respoce to client side
 * @param {any} res
 * @param {any} code
 * @param {any} errorMessage
 * @param {any} e=null
 * @return {any}
 */
function sendErrorResponse(res, code, errorMessage, e = null) {
  return res.status(code).send({
    status: 'error',
    error: errorMessage,
    e: e.details || e.getMessage(),
  });
}

/**
 * Send success responce to client side
 * @param {any} res
 * @param {any} code
 * @param {any} data
 * @param {any} message='Successful'
 * @return {any}
 */
function sendSuccessResponse(res, code, data, message = 'Successful') {
  return res.status(code).send({
    status: 'success',
    data,
    message,
  });
}
module.exports = {sendErrorResponse, sendSuccessResponse};
