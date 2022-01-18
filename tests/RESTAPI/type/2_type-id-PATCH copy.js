const supertest = require('supertest');
const validator = require('validator');
const assert = require('assert');
const is = require('is_js');

const request = supertest('http://127.0.0.1/fusionsuite/backend/index.php');

/**
* /v1/types endpoint
*/

describe('Update /v1/cmdb/types/:id', function() {
  it('update the Firewall type', function(done) {
    request
    .patch('/v1/cmdb/types/' + global.id)
    .send({name: 'FirewallNew'})
    .set('Accept', 'application/json')
    .set('Authorization', 'Bearer ' + global.token)
    .expect(200)
    .expect('Content-Type', /json/)
    .then(response => {
      assert(is.propertyCount(response.body, 0));
      done();
    })
    .catch(err => done(err));
  });

  it('update the Firewall type, but forget name => error', function(done) {
    request
    .patch('/v1/cmdb/types/' + global.id)
    .send({})
    .set('Accept', 'application/json')
    .set('Authorization', 'Bearer ' + global.token)
    .expect(400)
    .expect('Content-Type', /json/)
    .then(response => {
      assert(is.propertyCount(response.body, 2));
      assert(validator.equals(response.body.status, 'error'));
      assert(validator.equals(response.body.message, 'The Name is required'));
      done();
    })
    .catch(err => done(err));
  });

  it('update the Firewall type, but name not in right type => error', function(done) {
    request
    .patch('/v1/cmdb/types/' + global.id)
    .send({name: true})
    .set('Accept', 'application/json')
    .set('Authorization', 'Bearer ' + global.token)
    .expect(400)
    .expect('Content-Type', /json/)
    .then(response => {
      assert(is.propertyCount(response.body, 2));
      assert(validator.equals(response.body.status, 'error'));
      assert(validator.equals(response.body.message, 'The Name is not valid type'));
      done();
    })
    .catch(err => done(err));
  });

});

