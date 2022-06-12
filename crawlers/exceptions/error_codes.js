module.exports = {
    UNKNOWN_ERROR: -2000, // not used

    HANDLER_ERROR: -2010,
    WORKER_ERROR: -2020,
    ACTION_ERROR: -2030,

    MONGODB_ERROR: -3000,
    
    LOGIN_WORKER_ERROR: -4010,
    CONNECT_WORKER_ERROR: -4020,
    CONNECT_CHECK_WORKER_ERROR: -4030,
    MESSAGE_WORKER_ERROR: -4040,
    MESSAGE_CHECK_WORKER_ERROR: -4050,
    SCRIBE_WORKER_ERROR: -4060,
    SEARCH_WORKER_ERROR: -4070,
    VISIT_PROFILE_WORKER_ERROR: -4080,
    POST_ENGAGEMENT_WORKER_ERROR: -4090,
    GROUP_PARSING_WORKER_ERROR: -4090, //

    SN_SCRIBE_WORKER_ERROR: -4110,
    SN_SEARCH_WORKER_ERROR: -4120,

    CONTEXT_ERROR: -1, // Custom error that required user action to continue task
    NETWORK_ERROR: -2, //something wromg with network
    BAN_ERROR: -3, //Seems we are banned
    TOO_MANY_REDIRECTS_ERROR: -4, // need to update credentials
    
    LOGIN_ACTION_ERROR: -1010, //On our sid
    LOGIN_ERROR: -1011, //Check credentials
    LOGIN_PAGE_ERROR: -1012, //login page is not available Linkedin blocked us
    CONNECT_ACTION_ERROR: -1020, //Not used
    CONNECT_CHECK_ACTION_ERROR: -1030, //Not used
    MESSAGE_ACTION_ERROR: -1040, //Not used
    MESSAGE_CHECK_ACTION_ERROR: -1050, //Not used
    SCRIBE_ACTION_ERROR: -1060, //Not used
    SEARCH_ACTION_ERROR: -1070, //Can't open the next page, need to wait maybe because of limit

    // access errors
    SN_ACCESS_ERROR: -5100,
    PREMIUM_ACCESS_ERROR: -5200, // not used
    RL_ACCESS_ERROR: -5300, // not used

    
    CARRYOUT_SEARCH_ACTION_PAGES_FINISHED: 1000, //we have reached the last page for this search // not used


    // codes for user action
    SYSTEM_ERROR: -11, // not used from here
    UNKNOWN_PAGE_ERROR: -12,
    EMPTY_INPUT_ERROR: -13,

}