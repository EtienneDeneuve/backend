const supertest = require('supertest');
const validator = require('validator');
const assert = require('assert');
const is = require('is_js');

const request = supertest('http://127.0.0.1/fusionsuite/backend');

/**
* /v1/types endpoint
*/

describe('Delete /v1/cmdb/types/:id', function() {
  it('soft delete the Firewall type', function(done) {
    request
    .delete('/v1/cmdb/types/' + global.id)
    .set('Accept', 'application/json')
    .set('Authorization', 'Bearer ' + global.token)
    .expect('Content-Type', /json/)
    .expect(200, done)
  });

  it('permanently delete the Firewall type', function(done) {
    request
    .delete('/v1/cmdb/types/' + global.id)
    .set('Accept', 'application/json')
    .set('Authorization', 'Bearer ' + global.token)
    .expect('Content-Type', /json/)
    .expect(200, done)
  });

});

