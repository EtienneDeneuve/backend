const supertest = require('supertest');
const validator = require('validator');
const assert = require('assert');
const is = require('is_js');

const request = supertest('http://127.0.0.1/fusionsuite/backend/index.php');

/**
* /v1/types endpoint
*/
describe('Endpoint /v1/cmdb/types', function() {
  it('create a new type', function(done) {
    request
    .post('/v1/cmdb/types')
    .send({name: 'Firewall'})
    .set('Accept', 'application/json')
    .set('Authorization', 'Bearer ' + global.token)
    .expect(200)
    .expect('Content-Type', /json/)
    .then(response => {
      assert(is.propertyCount(response.body, 1));

      assert(is.integer(response.body.id));
      assert(validator.matches('' + response.body.id, /^\d+$/));
      global.id = response.body.id;
      done();
    })
    .catch(err => done(err));
  });

  it('create a new type, but exists => error', function(done) {
    request
    .post('/v1/cmdb/types')
    .send({name: 'Firewall'})
    .set('Accept', 'application/json')
    .set('Authorization', 'Bearer ' + global.token)
    .expect(409, done);
  });

  it('create a new type, but forget name => error', function(done) {
    request
    .post('/v1/cmdb/types')
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

  it('create a new type, but name not in right type => error', function(done) {
    request
    .post('/v1/cmdb/types')
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

