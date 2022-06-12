const models_shared = require("../models/shared.js");
const models = require("../models/models.js");

const SEARCH_URL_1 = "https://www.linkedin.com/search/results/all/?keywords=acronis&origin=GLOBAL_SEARCH_HEADER&page=97";
const SEARCH_URL_2 = "https://www.linkedin.com/sales/search/people?doFetchHeroCard=false&geoIncluded=103644278&industryIncluded=80&logHistory=false&page=98&preserveScrollPosition=false&rsLogId=343536385&searchSessionId=DY4JJTjhRH6qJ2ZZIKMtXw%3D%3D";

const CONNECT_URL_1 = "https://www.linkedin.com/in/kirill-shilov-25aa8630/";
const CONNECT_URL_2 = "https://www.linkedin.com/in/vlad-duplyakin-923475116/";
const CONNECT_URL_3 = "https://www.linkedin.com/in/alexander-savinkin-3ba99614/";
const CONNECT_URL_4 = "https://www.linkedin.com/in/bersheva/";
const CONNECT_URL_5 = "https://www.linkedin.com/in/alexyerokhin/";
const CONNECT_URL_6 = "https://www.linkedin.com/in/yana-k%C3%B6hler-44490693/";

const POST_URL_1 = "https://www.linkedin.com/posts/dr-marc-o-riain-74b7a911_covid-activity-6681268515862822913-CZrF/";
const POST_URL_2 = "https://www.linkedin.com/posts/modern-healthcare_appeals-court-rules-bankrupt-hospitals-not-activity-6681289160814325761-Kt_5/";
const POST_URL_3 = "https://www.linkedin.com/posts/christiangaravito_marketing-ventas-b2b-activity-6687284660688441344-iEXR/";
const POST_URL_4 = "https://www.linkedin.com/posts/swopelees_covid19-supportlocalbusiness-commercialrealestate-activity-6646040475419709440-00n4/";

const CONNECT_URL_SN_1 = "https://www.linkedin.com/sales/people/ACoAAAaQ6eYBBjpCMUzZRmnSoUQdVEoWsM8ld0E,name,Vv6O";
const CONNECT_URL_SN_2 = "https://www.linkedin.com/sales/people/ACwAABZc3xMB56tjV7WVgtLi-kdT3_NS55txCFk,NAME_SEARCH,mm5W?_ntb=DY4JJTjhRH6qJ2ZZIKMtXw%3D%3D";
const CONNECT_URL_SN_3 = "https://www.linkedin.com/sales/people/ACwAAAELuGkB3LJNWaqKJDuldS1kxVj1esMrLTo,OUT_OF_NETWORK,XJjv?_ntb=wByWqCcSSJOOu%2BxUHSUWOg%3D%3D";

// test task

(async () => {
  console.log("..... test_create_data started: .....", __filename);
  try {

    let account_id = "111113a80a2de70af2b11111"; // test id
    let task_id = "000003a80a2de70af2b00000"; // test id

    let task_data = {
      status: 1,
      is_queued: 0,
      action_key: 'linkedin-check-reply',
      input_data: {
        campaign_data: {
          post_url: POST_URL_3,
          search_url: SEARCH_URL_1,
          interval_pages: 4,
        },
        template_data: {
          message: 'Hi {first_name}, nice to meet you',
        },
        prospect_data: {
          first_name: '=)',
          last_name: '',
          company_title: 'howtotoken.com',
          linkedin: CONNECT_URL_6,
          linkedin_sn: CONNECT_URL_SN_3,
        }
      },
      credentials_id: account_id,
      ack: 0,
    }

    let account_data = {
      login: "ks.shilov@gmail.com",
      password: "Appatit_23843",
      cookies: [{
        name: "li_at",
        value: "AQEDARcxwXEBb18zAAABcflw5z8AAAFyHX1rP00AVzV1p6dd9IpqrPbIfjq5ajRuaeHm8ZvSGYaQccRo1fX80kr0WHCDWLOvuPfz-uiAn-dw631pZyHV2ZdU66bPPX4J--EXfE0IxqwMYTi8bIiWfL8U",
        domain: '.linkedin.com',
        path: "/",
        expires: Date.now() / 1000 + 10000000, // + ~ 4 months // https://www.epochconverter.com/
        size: 157,
        httpOnly: true,
        secure: true,
        session: false,
        sameSite: "None"
      }],
      expires: Date.now() / 1000 + 10000000, // + ~ 4 months // https://www.epochconverter.com/
      status: 0,
    }

    let account_data2 = {
      login: "mrgeen12358@gmail.com",
      password: "A123456a",
      cookies: [{
        name: "li_at",
        //value: "AQEDATErT40E2B8bAAABcyjdk58AAAFzTOoXn00Aqy0XYDO9vuw2Jt58tq9_GwPUYRJCYchRMNi113pjfVGkyc9EmhRCe24TQikhMAR6wN9J0cUHduPjF1ux2lqw-U-45MHxt4UHft7E8kJXW-jV0y7v",
        value: "AQEDATErT40A9GkCAAABc065QMgAAAFzcsXEyFYAWXRHW67coPBIGPkU5GEtbm-m9KsxWgluTR6BKfvqMkgbqf_Ji7_Njwvgz0kxUXZr6cmpg2o_j3otjjJaOdjACekecjOQgB93AcJoyMOJ05_CEJb2",
        domain: '.linkedin.com',
        path: "/",
        expires: Date.now() / 1000 + 10000000, // + ~ 4 months // https://www.epochconverter.com/
        size: 157,
        httpOnly: true,
        secure: true,
        session: false,
        sameSite: "None"
      }],
      expires: Date.now() / 1000 + 10000000, // + ~ 4 months // https://www.epochconverter.com/
      status: 0,
    }

    let account = await models.Accounts.findOneAndUpdate({ _id: account_id }, account_data2, { new: true, upsert: true });
    let task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, task_data, { new: true, upsert: true });

    console.log('..........account.............', account)
    console.log('..........task.............', task)

  } catch (err) {
    console.log('..........err.............', err.stack)
  }

})();