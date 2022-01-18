const supertest = require('supertest');
const validator = require('validator');
const assert = require('assert');
const is = require('is_js');

const request = supertest('http://127.0.0.1/fusionsuite/backend');

/**
* /v1/types endpoint
*/
describe('Endpoint /v1/cmdb/types', function() {
  it('respond with json containing the list of types', function(done) {
    request
    .get('/v1/cmdb/types')
    .set('Accept', 'application/json')
    .set('Authorization', 'Bearer ' + global.token)
    .expect(200)
    .expect('Content-Type', /json/)
    .then(response => {
      const secondItem = response.body[1];
      assert(is.propertyCount(secondItem, 7));
      assert(validator.equals('' + secondItem.id, '2'));
      assert(validator.equals(secondItem.name, 'Laptop'));
      assert(validator.equals(secondItem.modeling, 'physical'));
      done();
    })
    .catch(err => done(err));
  });


  // it('respond with json containing the type with id 1', function(done) {
  //   container
  //   .get('/1')
  //   .set('Accept', 'application/json')
  //   .expect('Content-Type', /json/)
  //   .expect(200, done);
  // });
// /1/property
// POST
// DELETE

// /1/items
// GET

// /1/propertygroups
// POST

});

